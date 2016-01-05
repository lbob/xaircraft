<?php

namespace Xaircraft;


/**
 * Class DI
 *
 * @package Xaircraft
 * @author lbob created at 2015/2/12 9:34
 */
class DI {

    /**
     * @var DI
     */
    private static $instance;

    private $instances = array();
    private $instanceParams = array();
    private $singletons = array();

    public static function bind($interface, $implement, array $params = null)
    {
        self::getInstance()->bindObject($interface, $implement, $params);
    }

    public static function bindSingleton($interface, $implement = null, array $params = null)
    {
        self::getInstance()->bindObjectSingleton($interface, $implement, $params);
    }

    public static function bindParam($interface, array $params)
    {
        self::getInstance()->bindObjectParam($interface, $params);
    }

    public static function get($interface, array $params = null)
    {
        return self::getInstance()->getObject($interface, $params);
    }

    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DI();
        }
        return self::$instance;
    }

    private function bindObject($interface, $implement, array $params = null)
    {
        $this->instances[$interface] = $implement;
        if (isset($params)) {
            $this->instanceParams[$interface] = $params;
        }
    }

    private function bindObjectSingleton($interface, $implement = null, array $params = null)
    {
        $this->singletons[$interface] = true;
        if (isset($implement)) {
            $this->bindObject($interface, $implement, $params);
        }
    }

    private function bindObjectParam($interface, array $params)
    {
        if (array_key_exists($interface, $this->instanceParams)) {
            $params = array_merge($this->instanceParams[$interface], $params);
        }

        $this->instanceParams[$interface] = $params;
    }

    /**
     * @param $name
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function getObject($name, array $params = null)
    {
        $className = $name;
        if (array_key_exists($name, $this->instances)) {
            $instance = $this->instances[$name];
            if (isset($instance)) {
                if (is_callable($instance)) {
                    return $this->createInstance($name, call_user_func($instance));
                }
                if (is_string($instance)) {
                    if ($instance !== '') {
                        $className = $instance;
                    } else {
                        throw new \Exception("绑定的对象为空 [$name]");
                    }
                } else {
                    return $instance;
                }
            }
        }
        if (class_exists($className)) {
            $class = new \ReflectionClass($className);
            $constructor = $class->getConstructor();
            if (!isset($constructor)) {
                return $this->createInstance($name, $class->newInstance());
            }
            $paramPrototypes = $class->getConstructor()->getParameters();
            if (empty($paramPrototypes)) {
                return $this->createInstance($name, $class->newInstance());
            }
            $injectParams = array();
            foreach ($paramPrototypes as $item) {
                $paramPrototypeClass = $item->getClass();
                if (isset($paramPrototypeClass)) {
                    if (isset($params) && !empty($params) && array_key_exists($item->name, $params)) {
                        $injectParams[] = $params[$item->name];
                    } else {
                        $injectParams[] = $this->getObject($paramPrototypeClass->getName());
                    }
                } else {
                    if (isset($params) && !empty($params) && array_key_exists($item->name, $params)) {
                        $injectParams[] = $params[$item->name];
                    } else if (array_key_exists($name, $this->instanceParams)) {
                        $innerParams = $this->instanceParams[$name];
                        if (isset($innerParams) && is_array($innerParams) && !empty($innerParams) && array_key_exists($item->name, $innerParams)) {
                            $injectParams[] = $innerParams[$item->name];
                        }
                    } else if ($item->isDefaultValueAvailable()) {
                        $defaultValue = $item->getDefaultValue();
                        $injectParams[] = $defaultValue;
                    } else if ($item->allowsNull()) {
                        $injectParams[] = null;
                    } else {
                        throw new \Exception("缺少参数 [$item->name]");
                    }
                }
            }
            return $this->createInstance($name, $class->newInstanceArgs($injectParams));
        }
        return null;
    }

    private function createInstance($name, $instance)
    {
        if (array_key_exists($name, $this->singletons)) {
            $this->instances[$name] = $instance;
        }
        return $instance;
    }
}

 