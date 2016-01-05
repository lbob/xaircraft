<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 6:54
 */

namespace Xaircraft\Core\IO;


class Directory
{
    public static function makeDir($dir)
    {
        if (isset($dir)) {
            if (stripos($dir, '\\') !== false) {
                $dir = str_replace('\\', '/', $dir);
            }
            $sections = explode('/', $dir);
            $path = array_shift($sections);
            foreach ($sections as $item) {
                $path .= '/' . $item;
                if (is_dir($path)) {
                    continue;
                } else {
                    mkdir($path);
                }
            }
            if (is_dir($dir)) {
                return $dir;
            }
            else {
                return false;
            }
        }
    }

    public static function traceDir($dir, $closure = null)
    {
        if (is_dir($dir) && $dh = opendir($dir)) {
            while (false !== ($file = readdir($dh))) {
                if (isset($closure) && is_callable($closure)) {
                    call_user_func($closure, $dir, $file);
                }
                $subDir = $dir . '/' . $file;
                if (is_dir($subDir) && "." !== $file && ".." !== $file) {
                    self::traceDir($subDir, $closure);
                }
            }
            closedir($dh);
        }
    }
}