<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/21
 * Time: 23:23
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class ModelException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_MODEL, $previous);
    }
}