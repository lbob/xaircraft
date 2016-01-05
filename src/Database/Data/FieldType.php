<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 19:49
 */

namespace Xaircraft\Database\Data;


abstract class FieldType
{
    const TEXT = 1;
    const NUMBER = 2;
    const DATE = 3;

    public abstract function convert($value, $args = null);

    public static function make($type, $field)
    {
        switch (strtoupper($type)) {
            case "CHAR":
            case "VARCHAR":
            case "TINYTEXT":
            case "TEXT":
            case "BLOB":
            case "MEDIUMTEXT":
            case "MEDIUMBLOB":
            case "LONGTEXT":
            case "LONGBLOB":
            case "ENUM":
            case "SET":
                return new TextFieldType();
            case "TINYINT":
            case "SMALLINT":
            case "MEDIUMINT":
            case "INT":
            case "BIGINT":
            case "FLOAT":
            case "DOUBLE":
            case "DECIMAL":
//                if (false !== array_search($field, array("create_at", "update_at", "delete_at"))) {
//                    return new TimestampFieldType();
//                }
                return new NumberFieldType();
            case "DATE":
            case "DATETIME":
            case "YEAR":
                return new DateFieldType();
            case "TIMESTAMP":
            case "TIME":
                return new TimestampFieldType();
            default:
                return null;
        }
    }
}