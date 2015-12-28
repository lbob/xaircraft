<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 17:50
 */

namespace Xaircraft\Module;


use Xaircraft\App;

class EnvironmentPathModule extends AppModule
{

    public function appStart()
    {
        App::path('migration', App::path('app') . '/database/migration');
        App::path('migration_history', App::path('migration') . '/history.dat');
        App::path('cache', App::path('app') . '/cache');
        App::path('exception', App::path('config') . '/exception.php');
        App::path('runtime', App::path('app') . '/runtime');
        App::path('daemon', App::path('config') . '/daemon.php');
        App::path('commands', App::path('config') . '/commands.php');
        App::path('log', App::path('app') . '/log');
    }

    public function handle()
    {
        // TODO: Implement handle() method.
    }

    public function appEnd()
    {
        // TODO: Implement appEnd() method.
    }
}