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
        $status = DaemonStatus::parse();
        if (!empty($status)) {
            /** @var DaemonStatus $item */
            foreach ($status as $item) {
                $pid = $item->getPid();
                $name = $item->getName();
                $status = $item->getStatus();
                $state = $status['State'];
                $stateDescription = array_key_exists('State_Description', $status) ? $status['State_Description'] : "";
                $vmSize = array_key_exists('Current_Virtual_Memory_Size', $status) ? $status['Current_Virtual_Memory_Size'] : "";
                Console::line("PID=$pid,Name=$name,State=$state($stateDescription),VmSize=$vmSize kB");
            }
        } else {
            Console::line('No daemons running.');
        }
    }
}