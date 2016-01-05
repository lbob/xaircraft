<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 14:41
 */
class Task
{
    private $closure;

    public function __construct($closure)
    {
        $this->closure = $closure;
    }

    public function invoke()
    {
        call_user_func($this->closure);
    }

    public function getClosure()
    {
        return $this->closure;
    }
}