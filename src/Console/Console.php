<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 9:23
 */

namespace Xaircraft\Console;


use Carbon\Carbon;
use Xaircraft\App;
use Xaircraft\Exception\ConsoleException;
use Xaircraft\Extensions\Log\Log;
use Xaircraft\Globals;

class Console
{
    public static function line($text)
    {
        if (self::isCliMode()) {
            Log::info("Console", "[" . Carbon::now()->toDateTimeString() . "]" . $text);
        } else {
            echo "[" . Carbon::now()->toDateTimeString() . "]" . $text . chr(10);
        }
    }

    public static function info($text)
    {
        if (self::isCliMode()) {
            Log::info("Console", $text);
        } else {
            echo $text;
        }
    }

    public static function error($text)
    {
        if (self::isCliMode()) {
            Log::info("Console", self::format($text, 'FAILURE'));
        } else {
            echo self::format($text, 'FAILURE');
        }
    }

    private static function format($text, $status)
    {
        $out = $status;
        return chr(27) . "$out:-->" . chr(10) . "$text" . chr(27) . "[0m" . chr(10);
    }

    private static function isCliMode()
    {
        if (Globals::RUNTIME_MODE_APACHE2HANDLER === App::environment(Globals::ENV_RUNTIME_MODE) ||
            Globals::RUNTIME_MODE_CGI_FCGI === App::environment(Globals::ENV_RUNTIME_MODE)) {
            return false;
        }
        return true;
    }
}