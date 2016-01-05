<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 14:16
 */

namespace Xaircraft\Module;


use Xaircraft\App;
use Xaircraft\Configuration\Settings;
use Xaircraft\Core\ClassLoader;

class AutoLoader extends AppModule
{
    /**
     * @var \Xaircraft\Core\ClassLoader
     */
    private $classLoader;

    public function appStart()
    {
        $this->classLoader = new ClassLoader();

        $paths = Settings::load('autoload');

        if (!isset($paths)) {
            $paths = array();
        }

        $paths[] = '/service';
        $paths[] = '/job';
        $paths[] = '/command';
        $paths[] = '/daemon';
        $paths[] = '/module';
        $paths[] = '/models';
        $paths[] = '/test';
        $paths[] = '/database/migration';

        foreach ($paths as $item) {
            $this->classLoader->addPath(App::path('app').$item);
        }
    }

    public function handle()
    {
        // TODO: Implement appStart() method.
    }

    public function appEnd()
    {
        // TODO: Implement appEnd() method.
    }
}