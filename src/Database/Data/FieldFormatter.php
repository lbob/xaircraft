<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 2016/3/10
 * Time: 12:00
 */

namespace Xaircraft\Database\Data;


class FieldFormatter
{
    private $fieldType;

    private $argument;

    private function __construct($fieldType, $argument = null)
    {
        $this->fieldType = $fieldType;
        $this->argument = $argument;
    }

    public static function create($fieldType, $argument = null)
    {
        return new FieldFormatter($fieldType, $argument);
    }

    public function getFieldType()
    {
        return $this->fieldType;
    }

    public function getArgument()
    {
        return $this->argument;
    }
}