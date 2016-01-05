<?php

namespace Xaircraft\Router;


/**
 * Class Filter
 *
 * @package Nebula
 * @author lbob created at 2014/11/28 9:52
 */
class Filter {
    /**
     * @var string
     */
    public $name;
    /**
     * @var string Filter 定义文件的路径
     */
    public $source;
    /**
     * @var array
     */
    public $handlers;

    public function __construct($name, $source, $handlers)
    {
        $this->name = $name;
        $this->source = $source;
        $this->handlers = $handlers;
    }
}

 