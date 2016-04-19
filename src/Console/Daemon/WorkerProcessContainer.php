<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/18
 * Time: 14:15
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Core\IO\Directory;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Json;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\IOException;

class WorkerProcessContainer
{
    const VERSION = '1.0';

    public static $pid;

    public static $status;

    public static $startFile;

    public static $statusFile;

    public static $logFile;

    public static $daemonize = false;

    public static $pidFile;

    public static $stdoutFile = '/dev/null';

    public static $name = '';

    protected static $workers = array();

    protected static $workerRestartCount = array();

    protected static $baseFolder;

    protected static $startAt;

    protected static $callClass;

    public static function run($name, array $workers, $daemonize = false)
    {
        self::$daemonize = $daemonize;
        self::$status = Worker::STATUS_STARTING;
        self::$name = $name;

        self::init();
        self::daemonize();
        self::saveMasterPid();
        //self::setProcessTitle("Xaircraft: worker process container start_class = " . get_called_class());
        self::installSignal();
        self::registerTicks();
        self::initWorkers($workers);
        self::displayUI();
        self::resetStd();
        self::monitorWorkers();
        self::stopAll();
    }

    public static function getPidFile($name)
    {
        $path = App::path('cache') .
            '/daemon/' .
            Strings::camelToSnake(str_replace('\\', '_', "container_" . get_called_class())) . '/' . $name . '/pid.dat';

        return $path;
    }

    public static function getStatusFile($name)
    {
        $path = App::path('cache') .
            '/daemon/' .
            Strings::camelToSnake(str_replace('\\', '_', "container_" . get_called_class())) . '/' . $name . '/status.dat';

        return $path;
    }

    protected static function init()
    {
        self::$callClass = "container_" . get_called_class();
        self::$baseFolder = App::path('cache') .
            '/daemon/' .
            Strings::camelToSnake(str_replace('\\', '_', self::$callClass)) . '/' . self::$name;
        self::$startAt = time();
        self::$logFile = self::$baseFolder . "/log/" . date("Y-m-d") . ".log";
        self::$statusFile = self::$baseFolder . "/status.dat";
        self::$pidFile = self::$baseFolder . "/pid.dat";

        Directory::makeDir(self::$baseFolder);
        Directory::makeDir(self::$baseFolder . "/log/");
        touch(self::$logFile);
        chmod(self::$logFile, 0622);
        touch(self::$statusFile);
        chmod(self::$statusFile, 0622);
    }

    protected static function daemonize()
    {
        if (!self::$daemonize) {
            return;
        }

        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception("fork fail");
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new \Exception("setsid fail");
        }
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception("fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
    }

    protected static function saveMasterPid()
    {
        self::$pid = posix_getpid();
        if (false === @file_put_contents(self::$pidFile, self::$pid)) {
            throw new \Exception('can not save pid to ' . self::$pidFile);
        }
        self::log('Master PID:' . self::$pid);
    }

    protected static function setProcessTitle($title)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } elseif (extension_loaded('proctitle') && function_exists('setproctitle')) {
            @setproctitle($title);
        }
    }

    protected static function installSignal()
    {
        pcntl_signal(SIGINT,  array(self::class, "signalHandler"), true);
        pcntl_signal(SIGTERM, array(self::class, "signalHandler"), true);
        pcntl_signal(SIGCHLD, array(self::class, "signalHandler"), true);
        pcntl_signal(SIGUSR1, array(self::class, "signalHandler"), true);
    }

    public static function reinstallSignal()
    {
        pcntl_signal(SIGINT,  SIG_IGN, false);
        pcntl_signal(SIGTERM, SIG_IGN, false);
        pcntl_signal(SIGCHLD, SIG_IGN, false);
        pcntl_signal(SIGUSR1, SIG_IGN, false);
    }

    public static function signalHandler($signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                self::stopAll();
                break;
            case SIGUSR1:
                self::writeStatus();
                break;
            case SIGCHLD:
                while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) != -1) {
                    self::log("SIGCHLD " . $pid);
                    if ($pid > 0) {
                        $status = pcntl_wtermsig($status);
                        if (array_key_exists($pid, self::$workers)) {
                            $worker = self::$workers[$pid];
                            $restartCount = self::$workerRestartCount[$worker->name];
                            if ($restartCount < $worker->restartLimit) {
                                self::forkOneWorker(self::$workers[$pid]);
                                self::$workerRestartCount[self::$workers[$pid]->name]++;
                                self::log(self::$workers[$pid]->name . "_LOG_RESTART_COUNT:" . self::$workerRestartCount[self::$workers[$pid]->name]);
                                unset(self::$workers[$pid]);
                            }
                        }
                    } else {
                        break;
                    }
                    sleep(1);
                    continue;
                }
                break;
        }
    }

    public static function registerTicks()
    {
        register_tick_function(function () {
            pcntl_signal_dispatch();
        });
    }

    protected static function initWorkers(array $workers)
    {
        self::$status = Worker::STATUS_RUNNING;
        if (!empty($workers)) {
            foreach ($workers as $worker) {
                self::$workerRestartCount[$worker->name] = 0;
                self::forkOneWorker($worker);
            }
        }
    }

    protected static function forkOneWorker(Worker $worker)
    {
        $pid = pcntl_fork();

        if (-1 === $pid) {
            self::log("Worker process fork fail.");
        }

        if (0 === $pid) {
            self::reinstallSignal();
            register_shutdown_function(array(self::class, "checkErrors"));

            $worker->run();

            exit(255);
        }

        if ($pid > 0) {
            self::$workers[$pid] = $worker;
        }
    }

    protected static function log($msg)
    {
        $msg = $msg . "\n";

        file_put_contents(self::$logFile, date('Y-m-d H:i:s') . " WorkerProcessContainer [PID=" . self::$pid . "] " . $msg, FILE_APPEND | LOCK_EX);
    }

    protected static function displayUI()
    {
        $content = "\033[1A\n\033[K-----------------------------\033[47;30m XAIRCRAFT \033[0m--------------------------------------------\n\033[0m";
        $content .= 'XAIRCRAFT DAEMON version:' . self::VERSION . "            PHP version:" . PHP_VERSION . "\n";
        $content .= "------------------------------\033[47;30m WORKERS \033[0m---------------------------------------------\n";
        $content .= "\033[47;30mpid\033[0m" . str_pad('', 7 - strlen('pid'));
        $content .= "\033[47;30mworker\033[0m" . str_pad('', 14 - strlen('worker'));
        $content .= "\033[47;30mrestart\033[0m" . str_pad('', 9 - strlen('restart'));
        $content .= "\033[47;30mshutdown\033[0m" . str_pad('', 10 - strlen('shutdown'));
        $content .= "\033[47;30mstart_at\033[0m" . str_pad('', 21 - strlen('start_at'));
        $content .= "\033[47;30mprocesses\033[0m \033[47;30m" . "status\033[0m\n";

        /** @var Worker $worker */
        foreach (self::$workers as $pid => $worker) {
            if (!posix_kill($pid, SIGUSR1)) {
                self::log("kill -SIGUSR1 $pid fail.");
            } else {
                self::log("kill -SIGUSR1 $pid success.");
            }
            sleep(3);

            $statusContent = file_get_contents($worker->statusFile);
            if (isset($statusContent)) {
                /** @var WorkerStatus $status */
                $status = Json::toObject($statusContent, WorkerStatus::class);
                if ($status) {
                    $content .= str_pad($pid, 7);
                    $content .= str_pad($worker->name, 14);
                    $content .= str_pad(self::$workerRestartCount[$worker->name], 9);
                    $content .= str_pad($status->shutdown_process_count, 10);
                    $content .= str_pad(date('Y-d-m H:i:s', $status->start_at), 21);
                    $content .= str_pad($status->process_count, 9);
                    $content .= "\033[32;40m [" . Worker::getStatus($status->status) . "] \033[0m\n";
                } else {
                    $content .= str_pad($pid, 7);
                    $content .= str_pad($worker->name, 14);
                    $content .= str_pad(self::$workerRestartCount[$worker->name], 9);
                    $content .= str_pad('unknow', 10);
                    $content .= str_pad('unknow', 21);
                    $content .= str_pad('unknow', 9);
                    $content .= "\033[32;40m [OK] \033[0m\n";
                }
            } else {
                $content .= str_pad($pid, 7);
                $content .= str_pad($worker->name, 14);
                $content .= str_pad(self::$workerRestartCount[$worker->name], 9);
                $content .= str_pad('unknow', 10);
                $content .= str_pad('unknow', 21);
                $content .= str_pad('unknow', 9);
                $content .= "\033[32;40m [unknow] \033[0m\n";
            }
        }
        $content .= "------------------------------------------------------------------------------------\n";
        echo $content;
        if (self::$daemonize) {
            global $argv;
            $start_file = $argv[0];
            echo "Input \"php $start_file stop\" to quit. Start success.\n";
        } else {
            echo "Press Ctrl-C to quit. Start success.\n";
        }
        return $content;
    }

    protected static function resetStd()
    {
        if (!self::$daemonize) {
            return;
        }
        global $STDOUT, $STDERR;
        $handle = fopen(self::$stdoutFile, "a");
        if ($handle) {
            unset($handle);
            @fclose(STDOUT);
            @fclose(STDERR);
            $STDOUT = fopen(self::$stdoutFile, "a");
            $STDERR = fopen(self::$stdoutFile, "a");
        } else {
            throw new \Exception('can not open stdoutFile ' . self::$stdoutFile);
        }
    }

    protected static function monitorWorkers()
    {
        while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) != -1) {
            pcntl_signal_dispatch();
            if ($pid > 0) {
                $status = pcntl_wtermsig($status);
                if (array_key_exists($pid, self::$workers)) {
                    $worker = self::$workers[$pid];
                    $restartCount = self::$workerRestartCount[$worker->name];
                    if ($restartCount < $worker->restartLimit) {
                        self::forkOneWorker(self::$workers[$pid]);
                        self::$workerRestartCount[self::$workers[$pid]->name]++;
                        self::log(self::$workers[$pid]->name . "_LOG_RESTART_COUNT:" . self::$workerRestartCount[self::$workers[$pid]->name]);
                        unset(self::$workers[$pid]);
                    }
                }
            }
            pcntl_signal_dispatch();
            sleep(1);
            continue;
        }

        self::stopAll();
    }

    public static function checkErrors()
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

            self::log($error_msg);
        }
    }

    protected static function stopAll()
    {
        self::$status = Worker::STATUS_SHUTDOWN;
        if (!empty(self::$workers)) {
            foreach (self::$workers as $pid => $worker) {
                posix_kill($pid, SIGINT);
                posix_kill($pid, SIGKILL);
            }
        }
        self::log("Stopped.");
        exit(250);
    }

    protected static function writeStatus()
    {
        if (!empty(self::$workers)) {
            foreach (self::$workers as $pid => $worker) {
                posix_kill($pid, SIGUSR1);
            }
        }

        $content = self::displayUI();
        file_put_contents(self::$statusFile, $content);
    }
}