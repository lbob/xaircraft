<?php
use Xaircraft\Async\Job;
use Xaircraft\Core\IO\File;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 15:38
 */
class HelloJob extends Job
{

    public function fire()
    {
        File::appendText(__DIR__ . '/output.txt', date("Y-m-d H:i:s") . "\r\n");
    }
}