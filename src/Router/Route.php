<?php

namespace Xaircraft\Router;


/**
 * Class Route
 *
 * @package Nebula
 * @author lbob created at 2014/11/27 17:01
 */
class Route {
    public $mappingName;
    public $namespace;
    public $expression;
    public $patterns;
    public $tokens;
    public $beforeHandlers = array();
    public $matchedHandlers = array();
    public $afterHandlers = array();
    public $defaultValues = array();
    public $beforeFilters = array();
    public $afterFilters = array();
}

 