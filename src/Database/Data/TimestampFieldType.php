<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 20:29
 */

namespace Xaircraft\Database\Data;


class TimestampFieldType extends FieldType
{

    public function convert($value, $args = null)
    {
        if (!isset($args)) {
            $args = "Y-m-d H:i:s";
        }
        return date($args, $value);
    }
}