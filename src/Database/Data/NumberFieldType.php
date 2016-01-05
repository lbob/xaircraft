<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 20:08
 */

namespace Xaircraft\Database\Data;


class NumberFieldType extends FieldType
{

    public function convert($value, $args = null)
    {
        return $value + 0;
    }
}