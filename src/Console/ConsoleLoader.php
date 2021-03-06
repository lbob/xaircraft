<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/4
 * Time: 17:39
 */

namespace Xaircraft\Console;


use Xaircraft\App;
use Xaircraft\Async\JobCommand;
use Xaircraft\Async\JobDaemon;
use Xaircraft\Authentication\AuthStorage;
use Xaircraft\Authentication\CacheAuthStorage;
use Xaircraft\Configuration\Settings;
use Xaircraft\Console\Daemon\Daemon;
use Xaircraft\Console\Daemon\DaemonCommand;
use Xaircraft\Console\Daemon\DaemonFactory;
use Xaircraft\Console\Daemon\IdleDaemon;
use Xaircraft\Console\Daemon\ScheduleDaemon;
use Xaircraft\Console\Daemon\ServiceCommand;
use Xaircraft\Database\Migration\MigrateCommand;
use Xaircraft\Database\Migration\MigrationCommand;
use Xaircraft\DB;
use Xaircraft\DI;
use Xaircraft\Exception\ConsoleException;
use Xaircraft\Globals;
use Xaircraft\Module\AppModule;
use Xaircraft\Nebula\Console\ModelCommand;
use Xaircraft\Queue\QueueCommand;

class ConsoleLoader extends AppModule
{
    public function enable()
    {
        if (Globals::RUNTIME_MODE_CLI !== App::environment(Globals::ENV_RUNTIME_MODE)) {
            return false;
        }
        if (stripos($_SERVER['PHP_SELF'], 'phpunit') > 0) {
            return false; //PHPUnit
        }
        return true;
    }

    public function appStart()
    {
        Command::bind('model', ModelCommand::class);
        Command::bind('migrate', MigrateCommand::class);
        Command::bind('migration', MigrationCommand::class);
        Command::bind('daemon', DaemonCommand::class);
        Command::bind('service', ServiceCommand::class);
        Command::bind('job', JobCommand::class);
        Command::bind('queue', QueueCommand::class);

        DaemonFactory::bind('idle', IdleDaemon::class);
        DaemonFactory::bind('job', JobDaemon::class);

        Settings::load('command');
        Settings::load('daemon');

        DI::bindSingleton(AuthStorage::class, CacheAuthStorage::class);
    }

    public function handle()
    {
        $command = Command::make($_SERVER['argc'], $_SERVER['argv']);

        /**
         * @var $command Command
         */
        if (isset($command)) {
            //Console::line("Start:");
            //Console::line("----------------------------------------");
            $command->handle();
            //Console::line("----------------------------------------");
            //Console::line("End.");
        }
    }

    public function appEnd()
    {
        DB::disconnect();
    }
}