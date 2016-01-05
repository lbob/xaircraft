<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/15
 * Time: 14:24
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class ArgumentInvalidException extends BaseException
{
    private $name;

    public function __construct($name = "", $message = "", \Exception $previous = null)
    {
        $this->name = $name;

        parent::__construct($message, Globals::EXCEPTION_ERROR_ARGUMENT_INVALID, $previous);
    }

    public function getName()
    {
        return $this->name;
    }
}