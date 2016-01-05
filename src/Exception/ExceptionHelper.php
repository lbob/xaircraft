<?php

namespace Xaircraft\Exception;


/**
 * Class ExceptionHelper
 *
 * @package Xaircraft\Exception
 * @author lbob created at 2015/3/19 10:17
 */
class ExceptionHelper {

    public static function ThrowIfNotTrue($boolean, $message = null)
    {
        if ($boolean) {
            return;
        }
        throw new \Exception($message);
    }

    public static function ThrowIfSpaceOrEmpty($string, $message = null)
    {
        if (!isset($string) || !is_string($string) || $string === '' || str_replace(' ', '', $string) === '') {
            throw new \Exception($message);
        }
    }

    public static function ThrowIfNullOrEmpty($value, $message = null)
    {
        if (!isset($value) || empty($value)) {
            throw new \Exception($message);
        }
    }

    public static function ThrowIfNotID($id, $message = null)
    {
        if (!(isset($id) && intval($id) > 0)) {
            throw new \Exception($message);
        }
    }

    public static function ThrowIfNotIds($ids, $message = null)
    {
        if (!isset($ids) || !is_array($ids) || empty($ids)) {
            throw new \Exception($message);
        }
        foreach ($ids as $id) {
            if (!isset($id) || $id <= 0) {
                throw new \Exception($message);
            }
        }
    }
}

 