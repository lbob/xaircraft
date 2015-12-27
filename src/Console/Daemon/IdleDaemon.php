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

    public function handle()
    {
        $status = file_get_contents('/proc/' . $this->getPid() . '/status');
        //$this->log('test', $status);
        Process::fork(function () {
            sleep(3);
            $this->log('child process', 'from child process.' . posix_getpid());
        });
        sleep(30);
    }

    public function beforeStart()
    {

    }

    public function beforeStop()
    {

    }
}