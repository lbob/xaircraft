<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/14
 * Time: 21:41
 */

namespace Xaircraft\Exception;


use Xaircraft\DB;
use Xaircraft\Globals;

class DatabaseException extends BaseException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, Globals::EXCEPTION_ERROR_DATABASE, $previous);
    }

    public function getDatabase()
    {
        return DB::getDatabaseName();
    }
}