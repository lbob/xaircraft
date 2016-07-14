<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2016/7/14
 * Time: 11:51
 */

namespace Xaircraft\Web;


use Xaircraft\DI;

class HttpModuleCollection
{
    /**
     * @var HttpModule[]
     */
    private $modules = array();

    public function register($name)
    {
        /** @var HttpModule $module */
        $module = DI::get($name);

        if ($module instanceof HttpModule) {
            $this->modules[] = $module;
        }
    }

    public function fireStart()
    {
        foreach ($this->modules as $module) {
            if (!$module->start()) {
                return false;
            }
        }
        return true;
    }

    public function fireEnd()
    {
        foreach ($this->modules as $module) {
            if (!$module->end()) {
                return false;
            }
        }
        return true;
    }
}