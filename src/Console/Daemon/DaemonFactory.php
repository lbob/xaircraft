<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/27
 * Time: 11:47
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\DI;
use Xaircraft\Exception\ConsoleException;

class DaemonFactory
{
    private static $daemons = array();

    public static function bind($name, $implement)
    {
        if (!isset(self::$daemons)) {
            self::$daemons = array();
        }
        self::$daemons[$name] = $implement;
    }

    public static function create($argc, array $argv)
    {
        if ($argc > 1) {
            $cmd = $name = $argv[2];
            if (array_key_exists($name, self::$daemons)) {
                $name = self::$daemons[$name];
            } else {
                $name = $name . 'Daemon';
            }
            unset($argv[0]);
            unset($argv[1]);
            unset($argv[2]);
            try {
                $sync = false;
                if (isset($argv[3])) {
                    if ("--sync" === $argv[3]) {
                        $sync = true;
                    }
                }
                $daemon = DI::get($name, array('args' => self::parseArgs($argv), 'sync' => $sync));
                if ($daemon instanceof Daemon) {
                    return $daemon;
                }
            } catch (\Exception $ex) {
                throw new ConsoleException("Daemon [$cmd] undefined.");
            }
            throw new ConsoleException("Class [$name] is not a Daemon.");
        }
        return null;
    }

    private static function parseArgs(array $args)
    {
        $results = array();
        foreach ($args as $arg) {
            if (preg_match('#(?<key>[a-zA-Z][a-zA-Z0-9\_]+)\=(?<value>[a-zA-Z0-9\_\\\/]+)#i', $arg, $matches)) {
                if (array_key_exists('key', $matches)) {
                    $results[$matches['key']] = array_key_exists('value', $matches) ? $matches['value'] : null;
                }
            } else {
                $results[] = $arg;
            }
        }

        return $results;
    }
}