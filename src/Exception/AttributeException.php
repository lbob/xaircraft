<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/8
 * Time: 9:39
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class AttributeException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_ATTRIBUTE, $previous);
    }
}