<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 19:24
 */

namespace Xaircraft\Queue;


use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\DI;

class QueueCommand extends Command
{

    public function handle()
    {
        /** @var QueueContext $context */
        $context = DI::get(QueueContext::class);
        QueueEvents::onStart($context);
        QueueEvents::onResumed($context);

        $resumes = $context->getResumeTasks();
        if (!empty($resumes)) {
            foreach ($resumes as $resume) {
                $resume->getContext()->setResume(true);
                $worker = Worker::createFromTask($resume);
                $worker->run();
            }
        }

        /** @var QueueItem $item */
        foreach (TaskQueue::waitPopAll(10) as $item) {
            if (isset($item)) {
                $worker = Worker::create($item->command, $item->params);
                $worker->run();
            } else {
                Console::line("Task Queue empty.");
            }
        }

        QueueEvents::onStop($context);
    }
}