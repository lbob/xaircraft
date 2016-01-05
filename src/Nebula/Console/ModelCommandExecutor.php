<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 8:34
 */

namespace Xaircraft\Nebula\Console;


use Xaircraft\App;
use Xaircraft\Exception\ConsoleException;

abstract class ModelCommandExecutor
{
    protected $args;

    private $path;

    public function __construct(array $args)
    {
        $this->args = $args;
        $this->path = $this->getModelPath();
    }

    public abstract function handle();

    public static function make(array $args)
    {
        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case '--all':
                    return null;
                default:
                    return null;
            }
        }
        return new SingleModelGenerator($args);
    }

    public function path($class, $namespace = null)
    {
        if (!isset($class) || !preg_match('#[a-zA-Z][a-zA-Z0-9\_]+#i', $class)) {
            throw new ConsoleException("Class [$class] error.");
        }
        $namespace = isset($namespace) ? strtolower($namespace) . "/" : "";
        return "$this->path/$namespace$class.php";
    }

    protected function checkArgs(array $requires)
    {
        foreach ($requires as $item) {
            if (!array_key_exists($item, $this->args)) {
                throw new ConsoleException("Invalid argument [$item].");
            }
        }
    }

    private function getModelPath()
    {
        $path = App::path('models');

        if (!isset($path)) {
            $path = App::path('app');
            if (isset($path)) {
                $path = "$path/models";
            } else {
                throw new ConsoleException("Can't find app path.");
            }
        }
        return $path;
    }
}