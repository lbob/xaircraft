<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/23
 * Time: 16:58
 */

namespace Xaircraft\Console\Daemon;



use Xaircraft\App;
use Xaircraft\Core\IO\File;

class IdleDaemon extends Daemon
{

    public function handle()
    {
        $status = file_get_contents('/proc/' . $this->getPid() . '/status');
        $this->log('test', $status);
        sleep(30);
    }

    public function beforeStart()
    {

    }

    public function beforeStop()
    {

    }
}