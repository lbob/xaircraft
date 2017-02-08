<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 17:01
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;

class Queue
{
    private static function getInstance()
    {
        /** @var BaseQueue $queue */
        $queue = DI::get(IQueue::class);
        return $queue;
    }

    public static function push($job, array $params = [], $cbUrl = '')
    {
        self::getInstance()->push($job, $params, $cbUrl);
    }

    public static function commit()
    {
        self::getInstance()->commit();
    }

    public static function mode()
    {
        return self::getInstance()->mode();
    }

    public static function rollback()
    {
        self::getInstance()->rollback();
    }
}