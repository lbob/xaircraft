<?php

namespace Xaircraft\Console\Daemon;
use Xaircraft\App;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Console\IPC\MessageQueue;
use Xaircraft\Console\Process;
use Xaircraft\Exception\ConsoleException;
use Xaircraft\Exception\DaemonException;
use Xaircraft\Globals;


/**
 * Class DaemonCommand
 *
 * @package Xaircraft\Console\Daemon
 * @author skyweo created at 15/12/19 21:41
 */
class DaemonCommand extends Command
{
    public function handle()
    {
        if (Globals::RUNTIME_MODE_CLI !== App::environment(Globals::ENV_RUNTIME_MODE)) {
            throw new ConsoleException("Only run in command line mode");
        }

        if (!function_exists("pcntl_signal")) {
            throw new ConsoleException("PHP does not appear to be compiled with the PCNTL extension.This is neccesary for daemonization");
        }
        if (function_exists("gc_enable")) {
            gc_enable();
        }

        Process::registerSignalHandler(function ($signal) {
            switch ($signal) {
                case SIGUSR1:
                    Console::line("SIGUSR1");
                    break;
                case SIGCHLD:
                    Console::line("SIGCHLD");
                    while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                        Console::line("SIGCHLD...");
                    }
                    break;
                case SIGTERM:
                case SIGHUP:
                case SIGQUIT:
                    Console::line("Exit...");
            }

        }, array(SIGTERM, SIGINT, SIGQUIT));

        Process::fork(new HelloWorker());

        Console::line("Exit.");
    }
}

 