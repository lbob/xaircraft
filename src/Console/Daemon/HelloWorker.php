<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/24
 * Time: 19:02
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\Console\Console;
use Xaircraft\Console\Worker;

class HelloWorker implements Worker
{

    public function beforeStart()
    {
        Console::line(posix_getpid() . " I'm before start.");
    }

    public function handle()
    {
        sleep(10);
        Console::line(posix_getpid() . " I'm handle.");
    }

    public function beforeStop()
    {
        Console::line(posix_getpid() . " I'm before stop.");
    }
}