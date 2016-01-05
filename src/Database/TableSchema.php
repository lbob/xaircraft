<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/14
 * Time: 21:06
 */

namespace Xaircraft\Database;


use Xaircraft\App;
use Xaircraft\Core\Container;
use Xaircraft\Core\IO\File;
use Xaircraft\Database\Data\FieldType;
use Xaircraft\Database\Symbol\TableSymbol;
use Xaircraft\Database\Validation\ValidateFactory;
use Xaircraft\Database\Validation\ValidationCollection;
use Xaircraft\DB;
use Xaircraft\Exception\DatabaseException;
use Xaircraft\Exception\DataTableException;
use Xaircraft\Exception\QueryException;

class TableSchema extends Container
{
    const SOFT_DELETE_FIELD = 'delete_at';
    const RESERVED_FIELD_UPDATE_AT = 'update_at';
    const RESERVED_FIELD_CREATE_AT = 'create_at';

    /**
     * @var TableSymbol
     */
    private $tableSymbol;

    private $columns;

    private $source;

    private $canSoftDelete = false;

    private $autoIncrementField;

    private $table;

    public function __construct($table)
    {
        if (!isset($table)) {
            throw new DataTableException($table, "Table name invalid - [$table]");
        }

        $this->tableSymbol = TableSymbol::create($table);
        $this->table = $this->tableSymbol->getName();

        $schema = App::path('schema');
        if (!isset($schema)) {
            throw new DatabaseException("Can't find schema cache directory.");
        }

        $this->source = $schema . '/' . DB::getDatabaseName() . '/' . $this->table . '.dat';

        $this->initialize();
    }

    public function getName()
    {
        return $this->table;
    }

    public function getSymbol()
    {
        return $this->tableSymbol->getSymbol();
    }

    public function getPrefix($withUnquote = true)
    {
        return $this->tableSymbol->getPrefix($withUnquote);
    }

    public function getFieldSymbol($field, $withUnquote = true)
    {
        if (false !== array_search($field, $this->columns())) {
            return $this->tableSymbol->getPrefix($withUnquote) . "." . ($withUnquote ? "`$field`" : $field);
        }
        throw new QueryException("Field [$field] not exists in table [$this->table].");
    }

    public function getAlias()
    {
        return $this->tableSymbol->getAlias();
    }

    public function getSoftDelete()
    {
        return $this->canSoftDelete;
    }

    public function getAutoIncrementField()
    {
        return $this->autoIncrementField;
    }

    public function fields()
    {
        return $this->columns;
    }

    public function columns()
    {
        $columns = array();
        foreach ($this->columns as $key => $value) {
            $columns[] = $key;
        }
        return $columns;
    }

    /**
     * @param $field
     * @return ColumnInfo
     */
    public function field($field)
    {
        return array_key_exists($field, $this->columns) ? $this->columns[$field] : null;
    }

    public function existsField($field)
    {
        if (isset($field)) {
            return array_key_exists($field, $this->columns);
        }

        return false;
    }

    private function initialize()
    {
        if (!$this->loadFromCache()) {
            $this->initializeColumns();
        }
    }

    private function initializeColumns()
    {
        $result = DB::query("SHOW FULL COLUMNS FROM `$this->table`");
        if (false === $result) {
            throw new DataTableException($this->table, "Table not exists - [$this->table]");
        }

        $this->columns = array();

        foreach ($result as $row) {
            $column = $this->parseColumn($row);
            $this->columns[$column->name] = $column;
        }
        $this->writeCache();
    }

    private function parseColumn($source)
    {
        $column = new ColumnInfo();
        $column->table = $this->table;
        $column->name = $source['Field'];
        $this->parseType($source['Type'], $column);
        $column->nullable = 'YES' === $source['Null'] ? true : false;
        $column->primary = 'PRI' === $source['Key'] ? true : false;
        $column->default = $source['Default'];
        $column->autoIncrement = !(false === strpos($source['Extra'], 'auto_increment'));
        $column->comment = $source['Comment'];
        $column->collationName = $source['Collation'];
        $column->validation = ValidateFactory::makeCollections($column->comment);
        $column->fieldType = FieldType::make($column->type, $column->name);

        if (self::SOFT_DELETE_FIELD === $column->name) {
            $this->canSoftDelete = true;
        }

        if ($column->autoIncrement) {
            $this->autoIncrementField = $column->name;
        }

        return $column;
    }

    private function parseType($source, ColumnInfo &$column)
    {
        if (preg_match('#(?<type>[a-zA-Z][a-zA-Z]+)(\((?<length>\d+)(\,(?<precision>\d+))?\))?#i', $source, $matches)) {
            $column->type = array_key_exists('type', $matches) ? $matches['type'] : null;
            $column->length = array_key_exists('length', $matches) ? $matches['length'] : null;
            $column->numericPrecision = array_key_exists('precision', $matches) ? $matches['precision'] : null;
            $column->numericUnsigned = !(false === strpos($source, 'unsigned'));
            $column->numericPrecision = array_key_exists('precision', $matches) ? $matches['precision'] : null;

            if ('enum' === strtolower($column->type)) {
                if (preg_match('#enum\((?<enums>(\'([a-zA-Z0-9\_]+)\',?)+)\)#i', $source, $matches)) {
                    $enums = str_replace("'", "", $matches['enums']);
                    $column->enums = explode(',', $enums);
                }
            }
        }
    }

    private function loadFromCache()
    {
        $cache = File::readText($this->source);
        if (false !== $cache) {
            $result = unserialize($cache);

            if (isset($result) && $result instanceof TableSchema) {
                $this->table = $result->table;
                $this->columns = $result->columns;
                $this->source = $result->source;
                $this->autoIncrementField = $result->autoIncrementField;
                $this->canSoftDelete = $result->canSoftDelete;

                return true;
            }
        }
        return false;
    }

    private function writeCache()
    {
        File::writeText($this->source, serialize($this));
    }
}