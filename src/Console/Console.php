<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 9:23
 */

namespace Xaircraft\Console;


use Xaircraft\Exception\ConsoleException;

class Console
{
    public static function line($text)
    {
        echo $text . chr(10);
    }

    public static function info($text)
    {
        echo $text;
    }

    public static function error($text)
    {
        echo self::format($text, 'FAILURE');
    }

    private static function format($text, $status)
    {
        $out = $status;
        return chr(27) . "$out:-->" . chr(10) . "$text" . chr(27) . "[0m" . chr(10);
    }
}