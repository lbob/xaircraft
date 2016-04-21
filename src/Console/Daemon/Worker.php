<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/18
 * Time: 14:16
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Core\IO\Directory;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\Log\Log;

abstract class Worker
{
    const STATUS_STARTING = 1;

    const STATUS_RUNNING = 2;

    const STATUS_SHUTDOWN = 4;

    const STATUS_RELOADING = 8;

    public $pid;

    public $name;

    public $user = '';

    public $statusFile;

    public $status;

    public $restartLimit = 0;

    public $restartCount = 0;

    protected $children;

    protected $shutdownChildrenPids = array();

    protected $logFile;

    protected $startFile;

    protected $startAt;

    protected $baseFolder;

    protected $args;

    protected $watchWorkerName = '';

    public abstract function onWorkerProcess();

    public abstract function getWorkerName();

    public abstract function getWorkerRestartLimit();

    public function __construct(array $args)
    {
        $this->name = $this->getWorkerName();
        Log::debug('Worker::__construct', $this->name);
        $this->restartLimit = $this->getWorkerRestartLimit();

        ExceptionHelper::ThrowIfSpaceOrEmpty($this->name, "Name of worker can't be null.");

        $this->baseFolder = App::path('cache') . '/daemon/workers/' . $this->name;
        $this->args = $args;

        $this->init();
    }

    public static function getStatus($status)
    {
        switch ($status) {
            case self::STATUS_STARTING:
                return 'STARTING';
            case self::STATUS_RUNNING:
                return 'RUNNING';
            case self::STATUS_SHUTDOWN:
                return 'SHUTDOWN';
            case self::STATUS_RELOADING:
                return 'RELOADING';
            default:
                return 'UNKNOW';
        }
    }

    public function run()
    {
        $this->status = self::STATUS_STARTING;
        $this->pid = posix_getpid();

        $this->log("Starting...");

        WorkerProcessContainer::setProcessTitle("Xaircraft: worker process start_class = " . $this->name);

        $this->installSignal();
        $this->registerTick();

        declare (ticks = 2) {
            $this->status = self::STATUS_RUNNING;
            $this->onWorkerProcess();
        }

        $this->monitorChildrenProcess();
        $this->stopAll(0);
    }

    public function fork(callable $handler)
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->log("child process fork fail.");
        }

        if ($pid === 0) {
            $this->log("child process starting...");
            WorkerProcessContainer::setProcessTitle("Xaircraft: worker child process start_class = " . $this->name);

            $this->reinstallSignal();
            register_shutdown_function(array($this, "checkErrors"));

            if (isset($handler)) {
                call_user_func($handler);
            }

            exit(250);
        }

        if ($pid > 0) {
            $this->children[$pid] = array(
                "status" => self::STATUS_RUNNING,
                "start_at" => time(),
                "shutdown_at" => 0
            );

            return $pid;
        }
    }

    protected function init()
    {
        $this->pid = posix_getpid();
        $this->startAt = time();

        $this->logFile = $this->baseFolder . "/log/" . date("Y-m-d") . ".log";
        $this->statusFile = $this->baseFolder . "/status.dat";
        Log::debug('Worker::init', $this->statusFile);

        Directory::makeDir($this->baseFolder);
        Directory::makeDir($this->baseFolder . "/log/");
        touch($this->logFile);
        chmod($this->logFile, 0622);
        touch($this->statusFile);
        chmod($this->statusFile, 0622);
    }

    protected function installSignal()
    {
        pcntl_signal(SIGINT,  array($this, "signalHandler"), true);
        pcntl_signal(SIGTERM, array($this, "signalHandler"), true);
        pcntl_signal(SIGCHLD, array($this, "signalHandler"), true);
        pcntl_signal(SIGUSR1, array($this, "signalHandler"), true);
    }

    protected function reinstallSignal()
    {
        pcntl_signal(SIGINT,  SIG_IGN, false);
        pcntl_signal(SIGTERM, SIG_IGN, false);
        pcntl_signal(SIGCHLD, SIG_IGN, false);
        pcntl_signal(SIGUSR1, SIG_IGN, false);
    }

    public function signalHandler($signal)
    {
        $this->log("SIGNAL = " . $signal);
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                $this->stopAll($signal);
                break;
            case SIGUSR1:
                $this->writeStatus();
                break;
            case SIGCHLD:
                $this->monitorChildrenProcess();
                break;
        }
    }

    protected function writeStatus()
    {
        $status = new WorkerStatus();
        $status->pid = $this->pid;
        $status->name = $this->name;
        $status->status = $this->status;
        $status->process_count = count($this->children);
        $status->shutdown_process_count = count($this->shutdownChildrenPids);
        $status->start_at = $this->startAt;

        $this->log($this->statusFile);
        $this->log(json_encode($status));

        @file_put_contents($this->statusFile, json_encode($status));
    }

    public function registerTick()
    {
        register_tick_function(function () {
            $this->log('ticks');
            pcntl_signal_dispatch();
        });
    }

    protected function monitorChildrenProcess()
    {
        pcntl_signal_dispatch();
        while ($pid = pcntl_waitpid(-1, $status, WNOHANG) != -1) {
            pcntl_signal_dispatch();
            if ($pid > 0) {
                $this->shutdownChildrenPids[] = array(
                    "status" => self::STATUS_SHUTDOWN,
                    "start_at" => isset($this->children[$pid]) ? $this->children[$pid]['start_at'] : 0,
                    "shutdown_at" => time()
                );
                if (isset($this->children[$pid])) {
                    unset($this->children[$pid]);
                }
            }
            pcntl_signal_dispatch();
            sleep(1);
            continue;
        }
    }

    protected function stopAll($signal)
    {
        $this->log("stopping...[SIGNAL=$signal]");

        if (!empty($this->children)) {
            foreach ($this->children as $pid => $status) {
                if ($status['status'] === self::STATUS_RUNNING) {
                    posix_kill($pid, SIGINT);
                    posix_kill($pid, SIGKILL);

                    $this->log("child process[PID=$pid] has been kill.");

                    $this->children[$pid]['status'] = self::STATUS_SHUTDOWN;
                    $this->children[$pid]['shutdown_at'] = time();
                }
            }
        }

        $this->log("stopped.");
        $this->status = self::STATUS_SHUTDOWN;
        $this->writeStatus();

        exit(250);
    }

    public function checkErrors()
    {
        $errors = error_get_last();

        if ($errors && ($errors['type'] === E_ERROR ||
                $errors['type'] === E_PARSE ||
                $errors['type'] === E_CORE_ERROR ||
                $errors['type'] === E_COMPILE_ERROR ||
                $errors['type'] === E_RECOVERABLE_ERROR)
        ) {
            $error_msg = "WORKER EXIT UNEXPECTED ";
            $error_msg .= Worker::getErrorType($errors['type']) . " {$errors['message']} in {$errors['file']} on line {$errors['line']}";

            $this->log($error_msg);
        }
    }

    protected function log($msg)
    {
        $msg = $msg . "\n";

        file_put_contents($this->logFile, date('Y-m-d H:i:s') . " Worker [$this->name][PID=$this->pid] " . $msg, FILE_APPEND | LOCK_EX);
    }

    public static function getErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return "";
    }
}