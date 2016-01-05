<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/8
 * Time: 14:20
 */

namespace Xaircraft\Exception;


use Xaircraft\Configuration\Settings;
use Xaircraft\Console\Console;
use Xaircraft\DI;

class ExceptionManager
{
    public static function handle(\Exception $ex)
    {
        if ($ex->getPrevious() instanceof ConsoleException) {
            Console::error($ex->getMessage());
            return;
        }

        $handles = Settings::load('exception');
        if (isset($handles) && !empty($handles)) {
            foreach ($handles as $key => $value) {
                if (self::recursive($ex, $key)) {
                    if (is_callable($value)) {
                        call_user_func($value, $ex);
                        return;
                    } else {
                        /** @var ExceptionHandle $handle */
                        $handle = DI::get($value);
                        if (isset($handle)) {
                            $handle->handle();
                            return;
                        }
                    }
                }
            }
        }

        throw $ex;
    }

    private static function recursive(\Exception $ex, $key)
    {
        if ($key === get_class($ex)) {
            return true;
        }
        $previous = $ex->getPrevious();
        if (isset($previous)) {
            return self::recursive($ex->getPrevious(), $key);
        }
        return false;
    }
}