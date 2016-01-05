<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 10:03
 */

namespace Xaircraft\Module;


use Xaircraft\Configuration\Settings;

class AppModuleLoader extends AppModule
{

    public function appStart()
    {
        Settings::load('module');
    }

    public function handle()
    {
        // TODO: Implement appEnd() method.
    }

    public function appEnd()
    {
        // TODO: Implement appEnd() method.
    }
}