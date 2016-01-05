<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 23:20
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class AuthenticationException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_AUTHENTICATION, $previous);
    }
}