<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/11
 * Time: 18:49
 */

namespace Xaircraft\Core\Collections;


class Generic
{
    public static function fast_array_filter($array, $pattern)
    {
        $pattern = '/' . preg_quote($pattern) . '/';
        return preg_grep($pattern, $array);
    }

    public static function fast_array_key_filter($array, $pattern)
    {
        $pattern = '/' . preg_quote($pattern) . '/';
        $keys = preg_grep($pattern, array_keys($array));
        $retArray = array_flip($keys);
        return array_intersect_key($array, $retArray);
    }
}