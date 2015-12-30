<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 15:07
 */

namespace Xaircraft\Async;


use Xaircraft\App;
use Xaircraft\Console\Daemon\Daemon;
use Xaircraft\Core\IO\File;

class JobDaemon extends Daemon
{

    public function beforeStart()
    {
        // TODO: Implement beforeStart() method.
    }

    public function beforeStop()
    {
        // TODO: Implement beforeStop() method.
    }

    public function handle()
    {
        $jobs = $this->getJobs();
        foreach ($jobs as $job) {
            /** @var Job $job */
            if ($job->time() <= time()) {
                $this->fork(function () use ($job) {
                    $this->log('Job', 'Job [' . $job->getID() . '] running.');
                    $job->fire();
                    if ($job->loop() > 0) {
                        while (true) {
                            sleep($job->loop());
                            $job->fire();
                        }
                    }
                });
            }
        }
    }

    private function getJobs()
    {
        $folder = App::path('async_job');
        while (true) {
            if (is_dir($folder) && $dh = opendir($folder)) {
                while (false !== ($file = readdir($dh))) {
                    $job = unserialize(File::readText($folder . '/' . $file));
                    yield $job;
                }
                closedir($dh);
            }
            sleep(1);
        }
    }
}