<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 17:11
 */

namespace Xaircraft\Queue;


use Xaircraft\App;
use Xaircraft\DI;
use Xaircraft\Module\AppModule;

class QueueAppModule extends AppModule
{
    public static $queue;

    public function appStart()
    {
        if (file_exists(App::path('queue'))) {
            require_once App::path('queue');
        }
        /** @var IQueue $queue */
        $queue = DI::get(IQueue::class);
        if (!isset($queue)) {
            DI::bindSingleton(IQueue::class, new SyncQueue());
            $queue = DI::get(IQueue::class);
        }

        if (file_exists(App::path('queue_handle'))) {
            $handles = require App::path('queue_handle');
            if (!empty($handles)) {
                if (!empty($handles['rollback'])) {
                    foreach ($handles['rollback'] as $handle) {
                        $queue->registerRollbackHandle($handle);
                    }
                }
                if (!empty($handles['commit'])) {
                    foreach ($handles['commit'] as $handle) {
                        $queue->registerCommitHandle($handle);
                    }
                }
            }
        }
    }

    public function handle()
    {
        // TODO: Implement handle() method.
    }

    public function appEnd()
    {
        Queue::commit();
    }
}