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
    public function __construct($message, $code = Globals::EXCEPTION_ERROR_AUTHENTICATION, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}