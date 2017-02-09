<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 13:26
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;

class SyncQueueImpl extends BaseQueue
{
    public function waitPopAll($timeout = 0)
    {
        // TODO: Implement waitPopAll() method.
    }

    public function onCommit()
    {
        /** @var QueueContext $context */
        $context = DI::get(QueueContext::class);
        QueueEvents::onStart($context);
        QueueEvents::onResumed($context);
        if (!empty($this->getItems())) {
            /** @var QueueItem $item */
            foreach ($this->getItems() as $item) {
                $worker = Worker::create($item->command, $item->params);
                $worker->run();
            }
        }
        QueueEvents::onStop($context);
    }
}