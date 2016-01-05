<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 23:21
 */

namespace Xaircraft\Exception;


class HttpAuthenticationException extends AuthenticationException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}