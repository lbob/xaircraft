<?php
use Xaircraft\App;
use Xaircraft\Globals;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 16:56
 */
class UbenchModule extends \Xaircraft\Module\AppModule
{
    public function enable()
    {
        if (Globals::RUNTIME_MODE_APACHE2HANDLER !== App::environment(Globals::ENV_RUNTIME_MODE)) {
            return false;
        }
        return true;
    }

    /**
     * @var Ubench
     */
    private $bench;

    public function appStart()
    {
        $this->bench = new Ubench();
        $this->bench->start();
    }

    public function handle()
    {
        // TODO: Implement handle() method.
    }

    public function appEnd()
    {
        $this->bench->end();
        echo '<p style="color:#a0a0a0;text-shadow:1px 1px 0 #FFFFFF;text-align:right;font-size:12px;padding-top:10px;">This page used <strong>' . $this->bench->getTime() . '</strong>, <strong>' . $this->bench->getMemoryUsage() . '</strong>.</p>';
    }
}