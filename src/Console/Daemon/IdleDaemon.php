<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/23
 * Time: 16:58
 */

namespace Xaircraft\Console\Daemon;



use Xaircraft\App;
use Xaircraft\Console\Process;
use Xaircraft\Core\IO\File;

class IdleDaemon extends Daemon
{
    public function onWorkerProcess()
    {
        declare (ticks = 2) {
            $this->fork(function () {
                $this->log("TEST" . time());
                sleep(10);
            });
            $this->fork(function () {
                $this->log("TEST" . time());
                sleep(10);
            });
            $this->fork(function () {
                $this->log("TEST" . time());
                sleep(10);
            });
            $this->fork(function () {
                $this->log("TEST" . time());
                sleep(10);
            });
            $this->fork(function () {
                $this->log("TEST" . time());
                sleep(10);
            });
            sleep(10);
        }
    }

    public function getWorkerName()
    {
        return "idle";
    }

    public function getWorkerRestartLimit()
    {
        return 3;
    }

    public function getMonitorPort()
    {
        return 998;
    }
}