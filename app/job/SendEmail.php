<?php
use Xaircraft\Queue\IJob;
use Xaircraft\Queue\Job;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 16:59
 */
class SendEmail implements IJob
{
    public function fire(array $params)
    {
        sleep(5);
        \Xaircraft\Extensions\Log\Log::debug('QUEUE_JOB', 'SendEmail', $params);
    }

    public function onResolved(Job $job)
    {
        \Xaircraft\Extensions\Log\Log::debug('IJOB.ONRESOLVED', $job->uid);
    }

    public function onRejected(Job $job, \Exception $ex = null)
    {
        \Xaircraft\Extensions\Log\Log::debug('IJOB.ONREJECTED', $job->uid);
    }
}