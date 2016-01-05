<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/4
 * Time: 23:51
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class ConsoleException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_CONSOLE, $previous);
    }
}