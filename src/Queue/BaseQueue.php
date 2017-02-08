<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 13:35
 */

namespace Xaircraft\Queue;


use Xaircraft\App;

abstract class BaseQueue implements IQueue
{
    const MODE_SYNC = 0;
    const MODE_ASYNC = 1;

    protected $queue = array();

    protected $rollbackHandles = array();

    protected $commitHandles = array();

    protected $isRollback = false;

    public abstract function mode();

    public abstract function push($job, array $params = null, $cbUrl = null);

    public abstract function waitPopAll($timeout = 0);

    public abstract function commit();

    public abstract function rollback();

    public function registerRollbackHandle(callable $handle)
    {
        $this->rollbackHandles[] = $handle;
    }

    public function registerCommitHandle(callable $handle)
    {
        $this->commitHandles[] = $handle;
    }

    public function onResolved(Job $job)
    {
        $config = require App::path('queue_handle');
        if (!empty($config)) {
            $fail = $config['resolve'];
            if (!isset($fail)) {
                call_user_func($fail, $job);
            }
        }
    }

    public function onRejected(Job $job)
    {
        $config = require App::path('queue_handle');
        if (!empty($config)) {
            $fail = $config['reject'];
            if (!isset($fail)) {
                call_user_func($fail, $job);
            }
        }
    }

    public static function event($name, array $params = [], callable $cb = null)
    {
        $config = require App::path('queue_handle');
        if (!empty($config)) {
            if (array_key_exists($name, $config) !== false) {
                $handle = $config[$name];
                if (isset($handle) && is_callable($handle)) {
                    $result = call_user_func_array($handle, $params);
                    if (isset($cb) && is_callable($cb)) {
                        call_user_func($cb, $result);
                    }
                    return $result;
                }
            }
        }
        return null;
    }
}