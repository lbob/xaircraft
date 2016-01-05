<?php

namespace Xaircraft\Core;
use Xaircraft\App;


/**
 * Class ClassLoader
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 20:17
 */
class ClassLoader extends Container
{
    private $blackList = array(
        'PHPUnit_Extensions_Story_TestCase'
    );

    private $paths = array();

    public function __construct()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function __destruct()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function addPath($path)
    {
        if (!in_array($path, $this->paths) && is_dir($path))
            $this->paths[] = $path;
    }

    public function addPaths(array $paths)
    {
        if (isset($paths)) {
            foreach ($paths as $item) {
                if (is_dir($item)) {
                    $this->addPath($item);
                }
            }
        }
    }

    private function loadClass($className)
    {
        if (isset($className) && !isset($this[$className])) {
            if ($this->checkBlackList($className)) {
                return;
            }
            $this[$className] = true;
            //$className        = strtolower($className);

            $path = null;
            // Controller
            if (strpos($className, "_controller") > 0) {
                $path = $this->getControllerPath(strtolower($className));
            }
            if (is_file($path) && is_readable($path)) {
                require_once $path;
            } else {
                $path = $this->scan($className);
                if (isset($path)) {
                    require_once $path;
                } else {
                    throw new \Exception("Can't load class [$className].");
                }
            }
        }
    }

    private function checkBlackList($className)
    {
        return array_search($className, $this->blackList) !== false;
    }

    private function getControllerPath($className)
    {
        $className = substr($className, 0, strpos($className, "_controller"));
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
        return App::path("app") . '/controllers/' . $className . '.php';
    }

    private function scan($className)
    {
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);

        $namespaceSeparatorIndex = strrpos($className, '\\');
        if ($namespaceSeparatorIndex !== false) {
            $namespace = substr($className, 0, $namespaceSeparatorIndex + 1);
            $class = substr($className, $namespaceSeparatorIndex + 1, (strlen($className) - $namespaceSeparatorIndex - 1));
            $className = strtolower($namespace) . $class;
            $className = str_replace('\\', '/', $className);
        }

        foreach ($this->paths as $path) {
            $file = $path . '/' . $className . '.php';
            if (is_file($file) && is_readable($file)) {
                return $file;
            }
        }
        return null;
    }
}

 