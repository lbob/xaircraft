<?php
use Xaircraft\Console\Console;
use Xaircraft\Queue\IJob;
use Xaircraft\Queue\ITask;
use Xaircraft\Queue\Job;
use Xaircraft\Queue\TaskContext;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 16:59
 */
class SendEmail implements ITask
{
    public function fire(TaskContext $context)
    {
        sleep(5);
        //throw new \Xaircraft\Exception\ModelException('aaadddfff');
        \Xaircraft\Extensions\Log\Log::debug('QUEUE_TASK', 'SendEmail', $context->getParams());
    }

    public function onBefore(TaskContext $context)
    {
        Console::line('TASK.onBefore: ' . $context->getUid());
    }

    public function onResolved(TaskContext $context)
    {
        Console::line('TASK.onResolved: ' . $context->getUid());
    }

    public function onRejected(TaskContext $context)
    {
        Console::line('TASK.onRejected: ' . $context->getUid());
    }

    public function onResume(TaskContext $context)
    {
        Console::line('TASK.onResume: ' . $context->getUid());
        return false;
    }
}