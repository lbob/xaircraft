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

    public static function guid($withHyphen = false)
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        if ($withHyphen) {
            $hyphen = chr(45);// "-"
        } else {
            $hyphen = "";
        }
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }

    public static function nonce($len = 4)
    {
        $str = null;
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $len; $i++) {
            $str .= $strPol[rand(0, $max)];
        }

        return $str;
    }
}
