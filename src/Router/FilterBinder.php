<?php

namespace Xaircraft\Router;


/**
 * Class FilterBinder
 *
 * @package Nebula
 * @author lbob created at 2014/12/1 9:33
 */
class FilterBinder {

    public $expression;

    public $mappingName;

    public $handlers = array();

    public function __construct()
    {
        $args = func_get_arg(0);
        if (count($args) > 0) {
            if (is_array($args[0])) {
                $this->expression = '^';
                foreach ($args[0] as $key => $value) {
                    if ($value === '*')
                        $value = '[^\]]*';
                    $this->expression = $this->expression . '\[' . $key . '\=' . $value . '\]';
                }
                $this->expression = '#'.$this->expression.'#i';
            }
            if (is_string($args[0])) {
                $this->mappingName = $args[0];
            }
        }
        if (count($args) > 1) {
            foreach ($args[1] as $key => $value) {
                if (is_callable($value))
                    $this->handlers[$key] = $value;
                if (is_string($value))
                    $this->handlers[$key] = explode('|', $value);
            }
        } else {
            throw new \InvalidArgumentException("Invalid filter name.");
        }
    }
}

 