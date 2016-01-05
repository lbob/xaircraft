<?php

namespace Xaircraft\Router;


/**
 * Class RouteResult
 *
 * @package Nebula
 * @author lbob created at 2014/11/28 9:24
 */
class RouteResult
{
    /**
     * @var array
     */
    public $params;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $mappingName;
    /**
     * @var boolean
     */
    public $isMatched = false;

    public function __construct($url, $mappingName, $isMatched, $params, $defaultValues)
    {
        $this->url         = $url;
        $this->mappingName = $mappingName;
        $this->isMatched   = $isMatched;

        if (isset($defaultValues)) {
            foreach ($params as $key => $value) {
                if (!isset($value) && array_key_exists($key, $defaultValues))
                    $params[$key] = $defaultValues[$key];
            }
        }

        $this->params = array_filter($params, function($var) {return isset($var);});
    }
}

 