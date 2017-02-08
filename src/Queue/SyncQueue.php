<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 14:56
 */

namespace Xaircraft\Queue;


class SyncQueue extends BaseQueue
{
    public function push($job, array $params = [], $cbUrl = null)
    {
        $this->queue[] = Job::create($job, $params, $cbUrl);

        return $this;
    }

    public function waitPopAll($timeout = 0)
    {
        if (!empty($this->queue)) {
            /** @var Job $item */
            foreach ($this->queue as $item) {
                $worker = Worker::create($item);
                $worker->run(function ($res) {

                }, function (\Exception $ex) {

                });
            }
        }
    }

    public function mode()
    {
        return self::MODE_SYNC;
    }

    public function commit()
    {
        if (!$this->isRollback) {
            $this->waitPopAll();

            if (!empty($this->commitHandles)) {
                foreach ($this->commitHandles as $commitHandle) {
                    call_user_func($commitHandle);
                }
            }
        }
    }

    public function rollback()
    {
        $this->isRollback = true;

        if (!empty($this->rollbackHandles)) {
            foreach ($this->rollbackHandles as $rollbackHandle) {
                call_user_func($rollbackHandle);
            }
        }
    }
}