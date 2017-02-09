<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/8
 * Time: 5:49
 */

use Xaircraft\Console\Console;
use Xaircraft\Queue\QueueContext;
use Xaircraft\Queue\QueueEvents;
use Xaircraft\Queue\RedisQueueImpl;
use Xaircraft\Queue\SyncQueueImpl;
use Xaircraft\Queue\Task;
use Xaircraft\Queue\TaskContext;

return array(
    'implement' => RedisQueueImpl::class,
    'redis' => array(
        "host" => "127.0.0.1",
        "port" => 6379,
        "database" => 0,
        "auth" => ""
    ),
    'event' => [
        QueueEvents::EVENT_ONSTART => function (QueueContext $context) {
            Console::line("EVENT_ONSTART");
        },
        QueueEvents::EVENT_ONSTOP => function (QueueContext $context) {
            Console::line("EVENT_ONSTOP");
        },
        QueueEvents::EVENT_ONRESUMED => function (QueueContext $context) {
            $taskContext = TaskContext::create('SendEmail', 'fire', array('resume....'), TaskContext::STATUS_NOSTART, 'asdf');
            $task = Task::create($taskContext);
            $context->appendResumeTasks($task);
            Console::line("EVENT_ONRESUMED");
        },
        QueueEvents::EVENT_ONTASKBEFOREFIRE => function (QueueContext $context) {
            Console::line("EVENT_ONTASKBEFOREFIRE: " . $context->getCurrentTask()->getContext()->getUid());
        },
        QueueEvents::EVENT_ONTASKRESOLVED => function (QueueContext $context) {
            Console::line("EVENT_ONTASKRESOLVED: " . $context->getCurrentTask()->getContext()->getUid());
        },
        QueueEvents::EVENT_ONTASKREJECTED => function (QueueContext $context) {
            Console::line("EVENT_ONTASKREJECTED: " . $context->getCurrentTask()->getContext()->getException()->getMessage());
        }
    ]
);