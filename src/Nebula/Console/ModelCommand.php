<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 0:06
 */

namespace Xaircraft\Nebula\Console;


use Xaircraft\Console\Command;
use Xaircraft\Console\Console;

class ModelCommand extends Command
{

    public function handle()
    {
        $command = ModelCommandExecutor::make($this->args);
        if (!isset($command)) {
            Console::error('Invalid arguments.');
            return;
        }
        $command->handle();
    }
}