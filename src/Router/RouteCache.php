<?php

namespace Xaircraft\Router;


/**
 * Class RouteCache
 *
 * @package Nebula
 * @author lbob created at 2014/11/28 11:01
 */
class RouteCache {
    public $routes = array();
    public $expired = true;

    private $isExpiredHandler;
    private $readCacheHandler;
    private $writeCacheHandler;
    private $isMustWriteCache = false;
    private $stat = array();

    public function registerIsExpiredHandler($handler)
    {
        $this->isExpiredHandler = $handler;
    }

    public function registerReadCacheHandler($handler)
    {
        $this->readCacheHandler = $handler;
    }

    public function registerWriteCacheHandler($handler)
    {
        $this->writeCacheHandler = $handler;
    }

    public function setData($key, $value)
    {
        $this->routes[$key] = $value;

        $this->isMustWriteCache = true;
    }

    public function getData($key)
    {
        if (isset($this->routes) && array_key_exists($key, $this->routes)) {
            $this->stat[$key] = true;
            return $this->routes[$key];
        }
        return null;
    }

    public function isCacheExpired($timestamp)
    {
        if ($this->isMustWriteCache)
            return true;
        if (isset($this->isExpiredHandler) && is_callable($this->isExpiredHandler))
            return call_user_func($this->isExpiredHandler, $timestamp);
        return true;
    }

    public function readCache()
    {
        if (isset($this->readCacheHandler) && is_callable($this->readCacheHandler))
            $this->routes = call_user_func($this->readCacheHandler);
    }

    public function writeCache()
    {
        if ($this->isMustWriteCache && isset($this->writeCacheHandler) &&  is_callable($this->writeCacheHandler))
            call_user_func($this->writeCacheHandler, $this->routes);
        $this->isMustWriteCache = false;
    }
}

 