<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 17:07
 */

namespace Xaircraft\Database\Migration;


use Xaircraft\App;
use Xaircraft\Configuration\Settings;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\ConsoleException;

class MigrationCommand extends Command
{
    private $history;

    public function handle()
    {
        if (isset($this->args[0])) {
            switch (strtolower($this->args[0])) {
                case "--make":
                    $this->makeMigration();
                    return;
                case "--history":
                    $this->historyMigration();
                    return;
                case "--forget":
                    $this->forgetMigration();
                    return;
            }
        }
        throw new ConsoleException("Please input migration command arguments: [--make][--history].");
    }

    private function makeMigration()
    {
        if (!isset($this->args[1])) {
            throw new ConsoleException("Invalid migration argument in [--make].");
        }
        $migration = $this->args[1];
        $author = isset($this->args[2]) ? $this->args[2] : "Unknown";
        $class = 'm' . time() . Strings::snakeToCamel($migration);
        $path = App::path('migration') . "/$class.php";
        File::writeText($path, Template::generate($class, $author));

        Console::line("migration [$migration] finished in [$class.php].");
    }

    private function historyMigration()
    {
        $history = Settings::get(App::path('migration_history'));
        if (isset($history)) {
            $history = unserialize($history);

            if (!empty($history)) {
                $this->history = $history;
                $date = "Unknown datetime";
                foreach ($this->history as $key => $value) {
                    if (preg_match('#^m(?<timestamp>\d+)#i', $value, $match)) {
                        if (array_key_exists('timestamp', $match)) {
                            $timestamp = intval($match['timestamp']);
                            $date = date("Y-m-d H:i:s", $timestamp);
                        }
                    }
                    Console::line("$key.[$date][$value]");
                }
            }
        }
    }

    private function forgetMigration()
    {
        if (!isset($this->args[1])) {
            throw new ConsoleException("Invalid migration argument in [--forget].");
        }

        $selectedIndex = intval($this->args[1]);

        $history = Settings::get(App::path('migration_history'));
        if (isset($history)) {
            $history = unserialize($history);

            if (!empty($history)) {
                $this->history = $history;
                $date = "Unknown datetime";
                foreach ($this->history as $key => $value) {
                    if ($key === $selectedIndex) {
                        unset($this->history[$selectedIndex]);
                        if (preg_match('#^m(?<timestamp>\d+)#i', $value, $match)) {
                            if (array_key_exists('timestamp', $match)) {
                                $timestamp = intval($match['timestamp']);
                                $date = date("Y-m-d H:i:s", $timestamp);
                            }
                        }
                        Settings::save(App::path('migration_history'), serialize($this->history));
                        Console::line("$key.[$date][$value] has forget.");
                        return;
                    }
                }
                Console::line("Can't find index [$selectedIndex].");
                return;
            }
        }
        Console::line("History is empty.");
    }
}