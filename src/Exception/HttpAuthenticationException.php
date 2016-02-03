<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 23:21
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class HttpAuthenticationException extends AuthenticationException
{
    public function __construct($message, $code = Globals::EXCEPTION_ERROR_AUTHENTICATION, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}