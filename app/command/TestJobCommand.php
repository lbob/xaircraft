<?php
use Xaircraft\Async\Job;
use Xaircraft\Console\Command;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 16:03
 */
class TestJobCommand extends Command
{

    public function handle()
    {
        $job = new HelloJob();
        Job::push($job);
    }
}