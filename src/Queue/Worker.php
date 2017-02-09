<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 11:16
 */

namespace Xaircraft\Queue;


use Predis\Client;
use Predis\Command\RedisFactory;
use Redis;
use Xaircraft\App;
use Xaircraft\DI;
use Xaircraft\Exception\ExceptionHelper;

class Worker
{
    /**
     * @var Job
     */
    private $job;

    private function __construct(Job $job)
    {
        $this->job = $job;
    }

    public static function create(Job $job)
    {
        return new Worker($job);
    }

    public function run(callable $resolve, callable $reject)
    {
        $this->onFireBefore();

        $className = $this->job->name;
        $method = $this->job->method;
        ExceptionHelper::ThrowIfNotTrue(class_exists($className), "Job class [$className] not exists.");
        ExceptionHelper::ThrowIfNotTrue(method_exists($className, $method), "Job [$className] method [$method] not exists.");
        $instance = DI::get($className);
        try {
            ExceptionHelper::ThrowIfNullOrEmpty($instance, "Job instance null.");
            $result = $instance->$method($this->job->params);
            //更新任务状态为：完成，并写入结果
            if (method_exists($instance, 'onResolved')) {
                $instance->onResolved($this->job);
            }
            if (isset($resolve)) {
                $resolve($result);
            }
            BaseQueue::event('onResolved', array($this->job));
        } catch (\Exception $e) {
            //更新任务状态为：异常，并写入异常信息
            //写入异常处理队列
            if (method_exists($instance, 'onRejected')) {
                $instance->onRejected($this->job, $e);
            }
            if (isset($reject)) {
                $reject($e);
            }
            BaseQueue::event('onRejected', array($this->job, $e));
        }

        $this->onFireAfter();
    }

    private function onFireBefore()
    {
        BaseQueue::event('onFireBefore', array('job' => $this->job));
    }

    private function onFireAfter()
    {
        BaseQueue::event('onFireAfter', array('job' => $this->job));
    }
}