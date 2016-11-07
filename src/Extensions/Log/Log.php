<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16-1-28
 * Time: 下午3:17
 */

namespace Xaircraft\Extensions\Log;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Xaircraft\App;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Net;

class Log
{
    public static function emergency($name, $message, array $context = array())
    {
        return self::record(Logger::EMERGENCY, $name, $message, $context);
    }

    public static function alert($name, $message, array $context = array())
    {
        return self::record(Logger::ALERT, $name, $message, $context);
    }

    public static function critical($name, $message, array $context = array())
    {
        return self::record(Logger::CRITICAL, $name, $message, $context);
    }

    public static function error($name, $message, array $context = array())
    {
        return self::record(Logger::ERROR, $name, $message, $context);
    }

    public static function warning($name, $message, array $context = array())
    {
        return self::record(Logger::WARNING, $name, $message, $context);
    }

    public static function notice($name, $message, array $context = array())
    {
        return self::record(Logger::NOTICE, $name, $message, $context);
    }

    public static function info($name, $message, array $context = array())
    {
        return self::record(Logger::INFO, $name, $message, $context);
    }

    public static function debug($name, $message, array $context = array())
    {
        return self::record(Logger::DEBUG, $name, $message, $context);
    }

    private static function record($level, $name, $message, array $context = array())
    {
        $levelName = Logger::getLevelName($level);
        $context = array_merge($context, self::getCommonContext());
        $path = self::getLogPath($levelName);
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler($path, $level));
        $method = ucfirst(strtolower($levelName));
        return $logger->addRecord($level, $message, $context);
        //return call_user_func(array($logger, "add$method"), $message, $context);
    }

    private static function getCommonContext()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP());
        } else {
            return array();
        }
    }

    private static function getLogPath($level)
    {
        return App::path('log') . '/' . $level . '/' . $level . '_' . date("Ymd", time()) . '.log';
    }
}