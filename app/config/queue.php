<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/8
 * Time: 5:49
 */

\Xaircraft\DI::bindSingleton(\Xaircraft\Queue\IQueue::class, function () {
    return new \Xaircraft\Queue\RedisQueue();
});