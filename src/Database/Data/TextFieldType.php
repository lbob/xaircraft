<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 20:03
 */

namespace Xaircraft\Database\Data;


class TextFieldType extends FieldType
{
    public function convert($value, $args = null)
    {
        return strval($value);
    }
}