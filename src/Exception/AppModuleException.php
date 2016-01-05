<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 10:31
 */

namespace Xaircraft\Exception;


class AppModuleException extends BaseException
{
    private $module;

    public function __construct($module = "", $message = "", $code = 0, \Exception $previous = null)
    {
        $this->module = $module;

        parent::__construct($message, $code, $previous);
    }

    public function getModule()
    {
        return $this->module;
    }
}