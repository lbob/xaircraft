<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/14
 * Time: 21:06
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Data\FieldType;
use Xaircraft\Database\Validation\ValidationCollection;

class ColumnInfo
{
    const FIELD_TYPE_ENUM = 'enum';

    public $name;

    public $type;

    public $default;

    public $length;

    public $comment;

    public $nullable = false;

    public $primary = false;

    public $autoIncrement = false;

    public $table;

    public $dateType;

    public $charMaxLength;

    public $charOctetLength;

    public $numericPrecision;

    public $numericScale;

    public $numericUnsigned;

    public $dateTimePrecision;

    public $charSetName;

    public $collationName;

    /**
     * @var ValidationCollection
     */
    public $validation;

    public $enums;

    /**
     * @var FieldType
     */
    public $fieldType;
}