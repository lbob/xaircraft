<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/19
 * Time: 20:25
 */

namespace Xaircraft\Database;


use Xaircraft\DB;
use Xaircraft\Exception\DataTableException;
use Xaircraft\Exception\FieldValidateException;

class InsertTableQueryExecutor extends TableQueryExecutor
{
    /**
     * @var TableSchema
     */
    private $schema;
    private $inserts;
    private $insertGetId;

    /**
     * InsertTableQueryExecutor constructor.
     * @param $schema
     * @param $inserts
     */
    public function __construct(TableSchema $schema, array $inserts, $insertGetId)
    {
        $this->schema = $schema;
        $this->inserts = $inserts;
        $this->insertGetId = $insertGetId;
    }

    public function execute(QueryContext $context)
    {
        $query = $this->toQueryString($context);

        if (true === $this->insertGetId) {
            if (true === DB::insert($query, $context->getParams())) {
                return DB::table($this->schema->getName())
                    ->orderBy($this->schema->getAutoIncrementField(), 'DESC')
                    ->pluck($this->schema->getAutoIncrementField())
                    ->execute();
            }
        }
        return DB::insert($query, $context->getParams());
    }

    public function toQueryString(QueryContext $context)
    {
        if (empty($this->inserts)) {
            throw new DataTableException($this->schema->getSymbol(), "Can't execute INSERT without inserts.");
        }

        if ($this->schema->existsField(TableSchema::RESERVED_FIELD_CREATE_AT) &&
            !array_key_exists(TableSchema::RESERVED_FIELD_CREATE_AT, $this->inserts)) {
            $this->inserts[TableSchema::RESERVED_FIELD_CREATE_AT] = time();
        }
        if ($this->schema->existsField(TableSchema::RESERVED_FIELD_UPDATE_AT) &&
            !array_key_exists(TableSchema::RESERVED_FIELD_UPDATE_AT, $this->inserts)) {
            $this->inserts[TableSchema::RESERVED_FIELD_UPDATE_AT] = time();
        }

        $columns = $this->schema->columns();
        foreach ($columns as $item) {
            if (!array_key_exists($item, $this->inserts) &&
                !$this->schema->field($item)->nullable &&
                !$this->schema->field($item)->autoIncrement) {
                throw new FieldValidateException(
                    $this->schema->getSymbol(),
                    $item,
                    "Field [$item] can't be null in table [" . $this->schema->getSymbol() . "]."
                );
            }
        }

        $fields = array();
        $values = array();

        foreach ($this->inserts as $key => $value) {
            if (!$this->schema->existsField($key)) {
                throw new FieldValidateException(
                    $this->schema->getSymbol(),
                    $key,
                    "Not exists field [$key] in table [" . $this->schema->getSymbol() . "]."
                );
            }
            $field = $this->schema->field($key);
            if ($field->autoIncrement) {
                throw new FieldValidateException(
                    $this->schema->getSymbol(),
                    $key,
                    "Can't set auto-increment field [$key] in insert query."
                );
            }
            if (ColumnInfo::FIELD_TYPE_ENUM === $field->type) {
                if (false === array_search($value, $field->enums)) {
                    throw new FieldValidateException(
                        $this->schema->getSymbol(),
                        $key,
                        "Not exists enum value [$value] in insert query. " .
                        "The value must be one of (" . implode(',', $field->enums) . ")."
                    );
                }
            }
            if (isset($field->validation) && !$field->validation->valid($value)) {
                throw new FieldValidateException(
                    $this->schema->getSymbol(),
                    $key,
                    "Field value validation error. [$key]"
                );
            }

            $fields[] = "`$key`";
            $values[] = "?";
            $context->param($value);
        }

        $statements = array(
            "INSERT INTO",
            $this->schema->getSymbol(),
            "(",
            implode(',', $fields),
            ")VALUES(",
            implode(',', $values),
            ")"
        );

        return implode(' ', $statements);
    }
}