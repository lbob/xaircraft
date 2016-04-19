<?php

namespace Xaircraft\Console\Daemon;
use Xaircraft\App;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Console\Process;
use Xaircraft\DB;
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

        DB::processModeOn();

        $daemon = DaemonFactory::create($_SERVER['argc'], $_SERVER['argv']);

        /**
         * @var $daemon Daemon
         */
        if (isset($daemon)) {
            $daemon->start();
            sleep(1);
            //Console::line("Daemon [" . $daemon->getName() . "] started.");
        }
    }
}

 