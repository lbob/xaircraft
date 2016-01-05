<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/27
 * Time: 14:52
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;

class DaemonStatus
{
    const STATE_RUNNING = 'R';
    const STATE_SLEEPING = 'S';
    const STATE_DEAD = 'D';

    private $pid;
    private $status = array();
    private $state;
    private $name;

    public function __construct($pid, $name)
    {
        $this->pid = $pid;
        $this->name = $name;

        $this->parseStatus();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public static function parse()
    {
        $path = App::path('runtime') . '/daemon/';
        $status = array();
        if ($dh = opendir($path)) {
            while (false !== ($file = readdir($dh))) {
                if (!preg_match('#[a-zA-Z\_\\\\/\-]+#i', $file)) {
                    continue;
                }
                $pid = intval(file_get_contents($path . $file));
                $name = str_replace('.pid', "", $file);
                $daemon = new DaemonStatus($pid, $name);
                $status[] = $daemon;
            }
            closedir($dh);
        }
        return $status;
    }

    private function parseStatus()
    {
        $path = '/proc/' . $this->pid . '/status';
        if (!posix_kill($this->pid, 0) || !file_exists($path)) {
            $this->status['State'] = self::STATE_DEAD;
            $this->state = self::STATE_DEAD;

            return;
        }

        $content = file_get_contents($path);

        if (preg_match('#State:[	 ]+(?<state>[a-zA-Z])( \((?<description>[a-zA-Z]+)\))?#i', $content, $match)) {
            $this->status['State'] = $match['state'];
            $this->status['State_Description'] = array_key_exists('description', $match) ? $match['description'] : null;
        }

        if (preg_match('#VmSize:[	 ]+(?<size>\d+) kB#i', $content, $match)) {
            $this->status['Current_Virtual_Memory_Size'] = array_key_exists('size', $match) ? $match['size'] : 0;
        }
    }
}