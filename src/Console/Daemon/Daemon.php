<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/27
 * Time: 10:32
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\DaemonException;

abstract class Daemon
{
    private $started = false;
    protected $singleton = true;
    protected $args;
    protected $folder;

    public function __construct(array $args)
    {
        $this->pidFilePath = App::path('runtime') . '/daemon/' . get_called_class() . '.pid';
        $this->args = $args;
        $this->folder = App::path('cache') . '/daemon/' . Strings::camelToSnake(str_replace('\\', '_', get_called_class()));

        $this->initialize();
    }

    public abstract function beforeStart();

    public abstract function beforeStop();

    public abstract function handle();

    public function start()
    {
        if ($this->started) {
            return;
        }

        $this->started = true;

        global $stdin, $stdout, $stderr;

        if ($this->singleton) {
            $this->checkPidFile();
        }

        umask(0);

        $pid = pcntl_fork();
        if (0 === $pid) {
            try {
                chdir("/");
                fclose(STDIN);fclose(STDOUT);fclose(STDERR);
                $stdin = fopen("/dev/null", "r");
                $stdout = fopen("/dev/null", "a");
                $stderr = fopen("/dev/null", "a");

                pcntl_signal(SIGTERM, SIG_DFL);
                pcntl_signal(SIGCHLD, SIG_DFL);

                if ($this->singleton) {
                    $this->createPidFile();
                }
                $this->beforeStart();
                $this->handle();
                $this->onStopping();
            } catch (\Exception $ex) {
                $this->onStopping();
                throw new DaemonException($this->getName(), $ex->getMessage(), $ex);
            }
        }
        if (-1 === $pid) {
            throw new DaemonException($this->getName(), "Daemon start failure.");
        }
    }

    public function stop()
    {
        if (!file_exists($this->pidFilePath)) {
            return;
        }
        $pid = file_get_contents($this->pidFilePath);
        $pid = intval($pid);
        if ($pid > 0 && posix_kill($pid, 0)) {
            $this->beforeStop();
            $this->onStopping();
            if (posix_kill($pid, SIGKILL)) {
                App::end();
                return;
            }
        }
        throw new DaemonException($this->getName(), "The daemon process end abnormally.");
    }

    protected function log($key, $message)
    {
        $path = $this->folder . '/log/' . date("Ymd", time()) . '.log';
        File::appendText($path, "[" . date("Y-m-d H:i:s", time()) . "] PID=" . $this->getPid() . " $key \r\n\r\n$message\r\n\r\n\r\n\r\n");
    }

    private function checkPidFile()
    {
        if (!file_exists($this->pidFilePath)) {
            return true;
        }
        $pid = file_get_contents($this->pidFilePath);
        $pid = intval($pid);
        if ($pid > 0 && posix_kill($pid, 0)) {
            throw new DaemonException($this->getName(), "The daemon process is already started.");
        } else {
            throw new DaemonException($this->getName(), "The daemon process end abnormally.");
        }
    }

    private function onStopping()
    {
        if (file_exists($this->pidFilePath)) {
            unlink($this->pidFilePath);
        }
    }

    public function getName()
    {
        return get_called_class();
    }

    public function getPid()
    {
        if (!file_exists($this->pidFilePath)) {
            return false;
        }
        $pid = file_get_contents($this->pidFilePath);
        return intval($pid);
    }

    private function createPidFile()
    {
        File::writeText($this->pidFilePath, posix_getpid());
    }

    private function initialize()
    {
        if (!function_exists('pcntl_signal_dispatch')) {
            declare(ticks = 10);
        }
        if (function_exists('gc_enable')) {
            gc_enable();
        }

        $this->registerSignalHandler(function ($signal) {
            switch ($signal) {
                case SIGCHLD:
                    while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                        $this->onChildProcessExisted($pid);
                    }
                    break;
                case SIGTERM:
                case SIGHUP:
                case SIGQUIT:
                    $this->onStopping();
                    App::end();
                    break;
                default:
                    return false;
            }
            return true;
        }, array(SIGTERM, SIGQUIT, SIGINT, SIGCHLD));
    }

    private function registerSignalHandler($closure, array $signals)
    {
        if (!empty($signals)) {
            foreach ($signals as $item) {
                pcntl_signal($item, $closure, false);
            }
        }
    }

    private function onChildProcessExisted($pid)
    {
        $this->log('child Process killed', 'from killed child process.' . $pid);
    }
}