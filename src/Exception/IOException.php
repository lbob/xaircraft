<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 6:54
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class IOException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_IO, $previous);
    }
}