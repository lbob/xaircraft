<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 17:12
 */

namespace Xaircraft\Database\Migration;


use Xaircraft\App;
use Xaircraft\Configuration\Settings;
use Xaircraft\Console\Command;
use Xaircraft\Console\Console;
use Xaircraft\Core\Strings;
use Xaircraft\DB;
use Xaircraft\DI;
use Xaircraft\Exception\ConsoleException;

class MigrateCommand extends Command
{
    private $history = array();

    public function handle()
    {
        if (!is_dir(App::path('migration'))) {
            throw new ConsoleException("Folder [app/database/migration] not exists.");
        }

        $this->initialize();

        $this->parseFolder();
    }

    private function initialize()
    {
        $history = Settings::get(App::path('migration_history'));
        if (isset($history)) {
            $history = unserialize($history);

            if (!empty($history)) {
                $this->history = $history;
            }
        }
    }

    private function parseFolder()
    {
        $path = App::path('migration');

        if ($dh = opendir($path)) {
            $migrations = array();
            while (false !== ($file = readdir($dh))) {
                $migrations[] = str_replace(".php", "", $file);
            }
            closedir($dh);

            sort($migrations);
            foreach ($migrations as $name) {
                if (false === array_search($name, $this->history)) {
                    $this->migrate($name);
                }
            }
        }
    }

    private function migrate($name)
    {
        if (!isset($name) || "" === $name || array_search($name, $this->history)) {
            return;
        }

        $migration = DI::get($name);
        if (!isset($migration) || !($migration instanceof Migration)) {
            return;
        }

        if (DB::transaction(function () use ($migration, $name) {
            return false !== $migration->up();
        })) {
            $this->recordHistory($name);
            $this->clearTableSchema($name);
            Console::line("migrate $name finished.");
        } else {
            Console::line("migrate $name failure.");
        }
    }

    private function recordHistory($name)
    {
        $this->history[] = $name;

        Settings::save(App::path('migration_history'), serialize($this->history));
    }

    private function clearTableSchema($name)
    {
        if (preg_match('#m[\d]+(Create|Alter)(?<table>[a-zA-Z][a-zA-Z0-9\_]+)Table#i', $name, $matches)) {
            $table = strtolower(Strings::camelToSnake($matches['table']));
            $path = App::path('cache') . "/schema/" . DB::getDatabaseName() . "/$table.dat";
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}