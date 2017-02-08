<?php

use Xaircraft\Extensions\Log\Log;
use Xaircraft\Queue\Job;

return array(
    'commit' => array(
        function () {
            Log::debug('HAHAHAHA', 'HAHAHAHA');
        },
        function () {
            Log::debug('HAHAHAHA2', 'HAHAHAHA2');
        }
    ),
    'rollback' => array(
        function () {
            Log::debug('HEIHEIHEI', 'HEIHEIHEI');
        },
        function () {
            Log::debug('HEIHEIHEI2', 'HEIHEIHEI2');
        }
    ),
    'fail' => function (Job $job) {
        Log::debug('QUEUE.FAIL', json_encode($job));
    },
    'onFireBefore' => function (Job $job) {
        Log::debug('QUEUE_onFireBefore', $job->uid);
    },
    'onFireAfter' => function (Job $job) {
        Log::debug('QUEUE_onFireAfter', $job->uid);
    },
    'onResolved' => function (Job $job) {
        Log::debug('QUEUE_onResolved', $job->uid);
    },
    'onRejected' => function (Job $job, Exception $ex) {
        Log::debug('QUEUE_onRejected', $job->uid);
    },
    'onRecover' => function () {
        Log::debug('QUEUE_onRecover', '');
        $job = Job::create('SendEmail', array('ccc'));
        return array($job);
    }
);