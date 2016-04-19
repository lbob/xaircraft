<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/19
 * Time: 15:46
 */

namespace Xaircraft\Console\Daemon;


class MonitorWorker extends Worker
{

    public function onWorkerProcess()
    {
        declare (ticks = 2) {
            while (1) {
                $this->log('monitor...');
                sleep(5);
            }
        }
    }

    public function getWorkerName()
    {
        return "monitor";
    }

    public function getWorkerRestartLimit()
    {
        return 3;
    }
}