<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 19:54
 */

namespace Xaircraft\Async;


use Xaircraft\App;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Core\IO\Directory;
use Xaircraft\Core\IO\File;
use Xaircraft\Exception\ConsoleException;

class JobCommand extends Command
{

    public function handle()
    {
        if (isset($this->args[0])) {
            switch (strtolower($this->args[0])) {
                case "--a":
                    $this->showAllJob();
                    return;
                case "--kill":
                    $this->killJob();
                    return;
            }
        }
        throw new ConsoleException("Please input job command arguments: [--a].");
    }

    private function showAllJob()
    {
        $index = 1;
        Directory::traceDir(App::path('async_job'), function ($dir, $file) use (&$index) {
            $path = $dir . '/' . $file;
            if (is_readable($path)) {
                /** @var Job $job */
                $job = unserialize(File::readText($path));
                if ($job instanceof Job) {
                    Console::line("[$index]JobName=" . $job->getName());
                    $index++;
                }
            }
        });
    }

    private function killJob()
    {
        if (!isset($this->args[1])) {
            throw new ConsoleException("Please input job index.");
        }
        $index = 1;
        Directory::traceDir(App::path('async_job'), function ($dir, $file) use (&$index) {
            $path = $dir . '/' . $file;
            if (is_readable($path)) {
                /** @var Job $job */
                $job = unserialize(File::readText($path));
                if ($job instanceof Job) {
                    if ($this->args[1] === $job->getName()) {
                        $job->terminate();
                        File::writeText($job->getPath(), serialize($job));
                    }
                    $index++;
                }
            }
        });
    }
}