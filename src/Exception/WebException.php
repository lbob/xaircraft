<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 16:11
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class WebException extends BaseException
{
    private $controller;

    private $action;

    public function __construct($controller, $action, $message = "", \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_WEB, $previous);

        $this->controller = $controller;
        $this->action = $action;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }
}