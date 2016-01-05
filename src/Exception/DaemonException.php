<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/24
 * Time: 11:43
 */

namespace Xaircraft\Exception;


use Xaircraft\Globals;

class DaemonException extends BaseException
{
    private $daemon;

    public function __construct($daemon, $message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_DAEMON, $previous);

        $this->daemon = $daemon;
    }

    public function getDaemon()
    {
        return $this->daemon;
    }
}