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
            echo "[" . Carbon::now()->toDateTimeString() . "]" . $text . chr(10);
        } else {
            Log::info("Console", "[" . Carbon::now()->toDateTimeString() . "]" . $text);
        }
    }

    public static function info($text)
    {
        if (self::isCliMode()) {
            echo $text;
        } else {
            Log::info("Console", $text);
        }
    }

    public static function error($text)
    {
        if (self::isCliMode()) {
            echo self::format($text, 'FAILURE');
        } else {
            Log::info("Console", self::format($text, 'FAILURE'));
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
