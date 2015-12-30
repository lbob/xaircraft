<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 15:38
 */
class HelloJob extends \Xaircraft\Async\Job
{

    public function fire()
    {
        \Xaircraft\Core\IO\File::appendText(__DIR__ . '/output.txt', date("Y-m-d H:i:s") . "\r\n");
    }
}