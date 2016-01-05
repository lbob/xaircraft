<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 15:25
 */

namespace Xaircraft\Core;


class Strings
{
    public static function htmlFilter($str)
    {
        if (is_string($str) && is_null(json_decode($str))) {
            return htmlspecialchars($str);
        } else {
            return $str;
        }
    }

    public static function snakeToCamel($value)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }

    public static function camelToSnake($value)
    {
        return strtolower(preg_replace_callback('/([a-z])([A-Z])/', create_function('$match', 'return $match[1] . "_" . $match[2];'), $value));
    }

    public static function guid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}