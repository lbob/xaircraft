<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 9:39
 */

namespace Xaircraft\Module;


use Xaircraft\DI;

abstract class AppModule
{
    /**
     * @var AppModuleState
     */
    public $state;

    private $className;

    public function __construct()
    {
        $this->state = DI::get(AppModuleState::class);
        $this->className = get_called_class();
    }

    public abstract function appStart();

    public abstract function handle();

    public abstract function appEnd();

    public function enable()
    {
        return true;
    }

    public function state()
    {
        return $this->state;
    }

    protected function set($key, $value)
    {
        $collections = $this->state[$this->className];
        if (!isset($collections) || !is_array($collections)) {
            $collections = array();
        }
        $collections[$key] = $value;
        $this->state[$this->className] = $collections;
    }

    protected function get($key, $module = null)
    {
        $root = $this->className;
        if (isset($module)) {
            $root = $module;
        }

        if ($this->state->offsetExists($root) && array_key_exists($key, $this->state[$root])) {
            return $this->state[$root][$key];
        }
        return null;
    }

    protected function stop()
    {
        $this->state->stop = true;
    }
}