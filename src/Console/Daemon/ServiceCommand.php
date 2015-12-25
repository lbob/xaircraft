<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/24
 * Time: 15:59
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Console\IPC\MessageQueue;
use Xaircraft\Exception\ConsoleException;

class ServiceCommand extends Command
{

    public function handle()
    {
        if (isset($this->args[0])) {
            switch (strtolower($this->args[0])) {
                case "--a":
                    $this->showAllDaemon();
                    return;
            }
        }
        throw new ConsoleException("Please input service command arguments: [--a].");
    }

    private function showAllDaemon()
    {

    }
}