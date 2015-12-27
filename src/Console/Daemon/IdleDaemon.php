<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/23
 * Time: 16:58
 */

namespace Xaircraft\Console\Daemon;



class IdleDaemon extends Daemon
{

    public function handle()
    {
        sleep(3);
    }

    public function beforeStart()
    {

    }

    public function beforeStop()
    {

    }
}