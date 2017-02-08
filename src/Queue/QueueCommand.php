<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 19:14
 */

namespace Xaircraft\Queue;


use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\DI;
use Xaircraft\Extensions\Log\Log;

class QueueCommand extends Command
{

    public function handle()
    {
        /** @var IQueue $queue */
        $queue = DI::get(IQueue::class);
        if ($queue->mode() === BaseQueue::MODE_SYNC) {
            Console::line('Queue run mode is SYNC.');
            return;
        }

        $recovers = BaseQueue::event('onRecover');
        if (!empty($recovers)) {
            foreach ($recovers as $recover) {
                if (isset($recover) && $recover instanceof Job) {
                    $worker = Worker::create($recover);
                    if (isset($worker)) {
                        $worker->run(function ($res) {
                            Console::line('Recover Job run success. RESULT: ' . $res);
                            Log::info('QUEUE_STATUS', 'Recover Job run success. RESULT: ' . $res);
                        }, function (\Exception $ex) {
                            Console::line('Recover Job run fail. RESULT: ' . $ex->getMessage());
                            Log::info('QUEUE_STATUS', 'Recover Job run fail. RESULT: ' . $ex->getMessage());
                        });
                    }
                }
            }
        }

        /** @var Job $item */
        foreach ($queue->waitPopAll(30) as $item) {
            if (isset($item)) {
                $worker = Worker::create($item);
                if (isset($worker)) {
                    $worker->run(function ($res) {
                        Console::line('Job run success. RESULT: ' . $res);
                        Log::info('QUEUE_STATUS', 'Job run success. RESULT: ' . $res);
                    }, function (\Exception $ex) {
                        Console::line('Job run fail. RESULT: ' . $ex->getMessage());
                        Log::info('QUEUE_STATUS', 'Job run fail. RESULT: ' . $ex->getMessage());
                    });
                }
            } else {
                Console::line('Job is empty. ');
                Log::info('QUEUE_STATUS', 'Job is empty. ');
            }
        }
    }
}