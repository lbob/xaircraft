<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 15:06
 */

namespace Xaircraft\Async;


use Xaircraft\App;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Globals;

abstract class Job
{
    private $id;
    private $time = 0;
    private $loop = 0;
    private $terminate = false;
    private $path;

    public function __construct()
    {
        $this->id = Strings::guid();
        $this->path = App::path('async_job') . '/' . $this->id . '.job';
    }

    public function getID()
    {
        return $this->id;
    }

    public function time($time = null)
    {
        if (isset($time)) {
            $this->time = $time;
        }

        return $this->time;
    }

    public function loop($seconds = null)
    {
        if (isset($seconds)) {
            $this->loop = $seconds;
        }

        return $this->loop;
    }

    public function terminate()
    {
        $this->terminate = true;
    }

    public function isTerminate()
    {
        return $this->terminate;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getName()
    {
        return get_called_class();
    }

    public abstract function fire();

    public static function push(Job $job)
    {
        $path = App::path('async_job') . "/" . $job->getID() . ".job";
        File::writeText($path, serialize($job));
    }
}