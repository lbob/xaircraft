<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 17:44
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;

class TaskQueue
{
    /**
     * @return BaseQueue
     */
    private static function getInstance()
    {
        return DI::get(BaseQueue::class);
    }

    public static function push($command, array $params = array())
    {
        self::getInstance()->push($command, $params);
    }

    public static function waitPopAll($timeout = 0)
    {
        return self::getInstance()->waitPopAll($timeout);
    }

    public static function commit()
    {
        self::getInstance()->commit();
    }

    public static function rollback()
    {
        self::getInstance()->rollback();
    }
}