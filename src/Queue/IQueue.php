<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 17:41
 */

namespace Xaircraft\Queue;


interface IQueue
{
    public function mode();

    public function push($job, array $params = null, $cbUrl = null);

    public function waitPopAll($timeout = 0);

    public function commit();

    public function rollback();

    public function registerRollbackHandle(callable $handle);

    public function registerCommitHandle(callable $handle);

    public function onResolved(Job $job);

    public function onRejected(Job $job);
}