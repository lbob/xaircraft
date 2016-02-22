<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 20:09
 */

namespace Xaircraft\Database\Data;


class DateFieldType extends FieldType
{

    public function convert($value, $args = null)
    {
        if ($value === 0) {
            return 0;
        }
        if (!isset($args)) {
            $args = "Y-m-d H:i:s";
        }
        return date($args, $value);
    }
}