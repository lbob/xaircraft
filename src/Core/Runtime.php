<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/11
 * Time: 19:29
 */

namespace Xaircraft\Core;


use Xaircraft\Globals;

class Runtime
{
    public static function getOS()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return Globals::OS_WIN;
        } else {
            return Globals::OS_LINUX;
        }
    }
}