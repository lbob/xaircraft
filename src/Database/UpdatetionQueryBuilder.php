<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/19
 * Time: 17:05
 */

namespace Xaircraft\Database;


use Xaircraft\Exception\DataTableException;
use Xaircraft\Exception\FieldValidateException;
use Xaircraft\Exception\QueryException;

class UpdatetionQueryBuilder
{
    public static function toString(TableSchema $schema, QueryContext $context, array $updates)
    {
        if (empty($updates)) {
            throw new DataTableException($schema->getSymbol(), "What do you want to update?");
        }

        if ($schema->existsField(TableSchema::RESERVED_FIELD_UPDATE_AT) &&
            !array_key_exists(TableSchema::RESERVED_FIELD_UPDATE_AT, $updates)) {
            $updates[TableSchema::RESERVED_FIELD_UPDATE_AT] = time();
        }

        $statements = array();

        foreach ($updates as $key => $value) {
            if (!$schema->existsField($key)) {
                throw new FieldValidateException(
                    $schema->getSymbol(),
                    $key,
                    "Not exists field [$key] in table [" . $schema->getSymbol() . "]."
                );
            }
            $field = $schema->field($key);
            if ($field->autoIncrement) {
                throw new FieldValidateException(
                    $schema->getSymbol(),
                    $key,
                    "Can't update auto-increment field [$key]."
                );
            }
            if (ColumnInfo::FIELD_TYPE_ENUM === $field->type) {
                if (false === array_search($value, $field->enums)) {
                    throw new FieldValidateException(
                        $schema->getSymbol(),
                        $key,
                        "Not exists enum value [$value] in insert query. " .
                        "The value must be one of (" . implode(',', $field->enums) . ")."
                    );
                }
            }

            if (isset($field->validation) && !$field->validation->valid($value)) {
                throw new FieldValidateException(
                    $schema->getSymbol(),
                    $key,
                    "Field value validation error. [$key]"
                );
            }

            $statements[] = "`$key` = ?";
            $context->param($value);
        }

        return !empty($statements) ? "SET " . implode(',', $statements) : null;
    }
}