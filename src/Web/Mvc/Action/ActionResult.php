<?php

namespace Xaircraft\Web\Mvc\Action;


/**
 * Class ActionResult
 *
 * @package Xaircraft\Mvc\Action
 * @author lbob created at 2014/12/6 12:00
 */
abstract class ActionResult {

    /**
     * @var array
     */
    public $data = array();

    public abstract function execute();
}
