<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/4
 * Time: 23:39
 */

namespace Xaircraft\Console;


use Xaircraft\DI;
use Xaircraft\Exception\ConsoleException;

abstract class Command
{
    protected $args;

    private static $commands = array();

    public function __construct(array $args = null)
    {
        $this->args = $args;
    }

    public function option($key)
    {
        if (array_key_exists($key, $this->args)) {
            return $this->args[$key];
        }
        return null;
    }

    public abstract function handle();

    public static function bind($name, $implement)
    {
        if (!isset(self::$commands)) {
            self::$commands = array();
        }
        self::$commands[$name] = $implement;
    }

    public static function make($argc, array $argv)
    {
        if ($argc > 1) {
            $cmd = $name = $argv[1];
            if (array_key_exists($name, self::$commands)) {
                $name = self::$commands[$name];
            } else {
                $name = $name . 'Command';
            }
            unset($argv[0]);
            unset($argv[1]);
            try {
                $command = DI::get($name, array('args' => self::parseArgs($argv)));
                if ($command instanceof Command) {
                    return $command;
                }
            } catch (\Exception $ex) {
                throw new ConsoleException("Command [$cmd] undefined.");
            }
            throw new ConsoleException("Class [$name] is not a Command.");
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