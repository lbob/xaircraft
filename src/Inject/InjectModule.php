<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 17:41
 */

namespace Xaircraft\Inject;


use Xaircraft\Configuration\Settings;
use Xaircraft\Module\AppModule;

class InjectModule extends AppModule
{

    public function appStart()
    {
        Settings::load('inject');
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