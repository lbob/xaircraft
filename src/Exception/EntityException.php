<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 15:15
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class EntityException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_ENTITY, $previous);
    }
}