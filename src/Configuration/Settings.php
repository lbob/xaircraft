<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 12:30
 */

namespace Xaircraft\Configuration;


use Xaircraft\App;
use Xaircraft\Core\IO\File;

class Settings
{
    public static function load($key)
    {
        $path = App::path($key);

        if (isset($path) && is_readable($path)) {
            return require $path;
        }
        return null;
    }

    public static function save($path, $content)
    {
        if (isset($path)) {
            File::writeText($path, $content);
        }
    }

    public static function get($path)
    {
        if (isset($path) && is_readable($path)) {
            return File::readText($path);
        }
        return null;
    }
}