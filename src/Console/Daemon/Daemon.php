<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/27
 * Time: 10:32
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Console\Process;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\DaemonException;

abstract class Daemon extends Worker
{
    protected $singleton = true;
    protected $args;
    private $sync;

    public function __construct(array $args, $sync = false)
    {
        parent::__construct($args);
        $this->args = $args;
        $this->sync = $sync;
    }

    public function start()
    {
        if (empty($this->args)) {
            echo "Please input daemon name with [start|stop|status]\n";
            return;
        }
        $pidPath = WorkerProcessContainer::getPidFile($this->name);
        // Get master process PID.
        $master_pid      = @file_get_contents($pidPath);
        $master_is_alive = $master_pid && @posix_kill($master_pid, 0);
        // Master is still alive?
        if ($master_is_alive) {
            if ($this->args[0] === 'start') {
                self::log("Worker[$this->name] already running");
                echo ("Worker[$this->name] already running.\n");
                exit;
            }
        } elseif ($this->args[0] !== 'start' && $this->args[0] !== 'restart') {
            self::log("Worker[$this->name] not run");
            echo "Worker[$this->name] not run.\n";
        }
        switch ($this->args[0]) {
            case 'start':
                WorkerProcessContainer::run($this->name, array($this, new MonitorWorker(array())), !$this->sync);
                break;
            case 'status':
                $this->status();
                break;
            case 'stop':
                $this->stop();
                break;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function status()
    {
        $pidPath = WorkerProcessContainer::getPidFile($this->name);
        if (file_exists($pidPath)) {
            $pid = file_get_contents($pidPath);
            if (!posix_kill(intval($pid), SIGUSR1)) {
                echo "kill -s SIGUSR1 $pid [$this->name] fail.\n";
            } else {
                $statusPath = WorkerProcessContainer::getStatusFile($this->name);
                if (file_exists($statusPath)) {
                    $content = file_get_contents($statusPath);
                    echo $content . "\n";
                } else {
                    echo "load status fail.\n";
                }
            }
        }
    }

    public function stop()
    {
        $pidPath = WorkerProcessContainer::getPidFile($this->name);
        if (file_exists($pidPath)) {
            $pid = file_get_contents($pidPath);
            if (!posix_kill(intval($pid), SIGINT) && !posix_kill(intval($pid), SIGKILL)) {
                echo "kill -s SIGINT|SIGKILL $pid [$this->name] fail.\n";
            } else {
                echo "kill -s SIGINT|SIGKILL $pid [$this->name] success.\n";
            }
        }
    }
}