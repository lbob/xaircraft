<?php
/**
 * Xaircraft PHP Framework version 1.1
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/11
 * Time: 11:11
 */

namespace Xaircraft;

use Xaircraft\Configuration\Settings;
use Xaircraft\Console\Console;
use Xaircraft\Console\ConsoleLoader;
use Xaircraft\Core\Container;
use Xaircraft\Exception\ConsoleException;
use Xaircraft\Exception\ExceptionManager;
use Xaircraft\Inject\InjectModule;
use Xaircraft\Module\AppModuleLoader;
use Xaircraft\Module\AppModuleState;
use Xaircraft\Core\Runtime;
use Xaircraft\Exception\AppModuleException;
use Xaircraft\Module\AutoLoader;
use Xaircraft\Module\EnvironmentPathModule;
use Xaircraft\Web\WebAppModule;

class App extends Container
{
    private static $app;

    /**
     * @var array Base paths
     */
    private $paths;

    private $environment = array();

    private $appModules = array();

    private $killedAppModules = array();

    private $currentModule;

    public function run()
    {
        if (isset($this->appModules) && !empty($this->appModules)) {
            try {
                DI::bindSingleton(AppModuleState::class, new AppModuleState());
                $this->fireAppModule('appStart');
                $this->fireAppModule('handle');
                $this->fireAppModule('appEnd');
            } catch (\Exception $ex) {
                $this->onError(new AppModuleException($this->currentModule, $ex->getMessage(), $ex->getCode(), $ex));
            }
        }
    }

    private function fireAppModule($action)
    {
        if (!isset($action) || false === array_search($action, array('appStart', 'handle', 'appEnd'))) {
            return;
        }

        $index = 0;
        while ($index < count($this->appModules)) {
            $this->currentModule = $this->appModules[$index];
            $index++;
            if (false !== array_search($this->currentModule, $this->killedAppModules)) {
                continue;
            }
            /**
             * @var $module \Xaircraft\Module\AppModule
             */
            $module = DI::get($this->currentModule);
            if (true === $module->enable()) {
                call_user_func(array($module, $action));
            }

            $state = $module->state();
            if ($state->stop) {
                break;
            }
        }
    }

    public function bindPaths($paths)
    {
        if (isset($paths)) {
            if (is_array($paths) && !empty($paths)) {
                $this->paths = $paths;
            } else if (is_readable($paths) && $paths !== '') {
                $this->paths = require_once $paths;
            }
        }
    }

    public static function instance()
    {
        if (!isset(self::$app)) {
            self::$app = new App();
            self::$app->initialize();
        }
        return self::$app;
    }

    public static function path($key, $value = null)
    {
        $path = self::instance()->getPath($key);
        if (!isset($path) && isset($value)) {
            $path = $value;
            self::instance()->paths[$key] = $value;
        }
        return $path;
    }

    public static function environment($key, $value = null)
    {
        if (isset($value)) {
            self::instance()->setEnvironment($key, $value);
        }
        return self::instance()->getEnvironment($key);
    }

    public static function module($module)
    {
        DI::bindSingleton($module, $module);
        if (false === array_search($module, self::instance()->appModules)) {
            self::instance()->appModules[] = $module;
        }
        $killedIndex = array_search($module, self::instance()->killedAppModules);
        if (false !== $killedIndex) {
            unset(self::instance()->killedAppModules[$killedIndex]);
        }
    }

    public static function killModule($module)
    {
        if (false !== array_search($module, self::instance()->appModules)) {
            self::instance()->killedAppModules[] = $module;
        }
    }

    public static function end($code = 0)
    {
        exit($code);
    }

    private function initialize()
    {
        $this->initializeEnvironment();
        $this->initializeBaseModules();
    }

    private function getPath($key)
    {
        if (isset($this->paths) && isset($key) && array_key_exists($key, $this->paths)) {
            return $this->paths[$key];
        }
        return null;
    }

    private function initializeEnvironment()
    {
        if (!isset($this->environment) || empty($this->environment)) {
            $this->environment[Globals::ENV_FRAMEWORK] = 'Xaircraft';
            $this->environment[Globals::ENV_VERSION] = '1.1';
            $this->environment[Globals::ENV_MODE] = 'dev';
            $this->environment[Globals::ENV_HOST] = '';
            $this->environment[Globals::ENV_RUNTIME_MODE] = php_sapi_name();
            $this->environment[Globals::ENV_OS] = Runtime::getOS();
            $this->environment[Globals::ENV_OS_INFO] = php_uname();
            $this->environment[Globals::ENV_MVC_VIEW_FILE_EXTENSION] = 'phtml';
            $this->environment[Globals::ENV_DATABASE_PROVIDER] = Globals::DATABASE_PROVIDER_PDO;
        }
    }

    private function initializeBaseModules()
    {
        self::module(AutoLoader::class);
        self::module(InjectModule::class);
        self::module(AppModuleLoader::class);
        self::module(EnvironmentPathModule::class);
        self::module(WebAppModule::class);
        self::module(ConsoleLoader::class);
    }

    private function getEnvironment($key)
    {
        if (isset($key) && array_key_exists($key, $this->environment)) {
            return $this->environment[$key];
        }
        return null;
    }

    private function setEnvironment($key, $value)
    {
        if (isset($key) && array_key_exists($key, $this->environment) && isset($value)) {
            if (array_key_exists($key, array(
                Globals::ENV_FRAMEWORK,
                Globals::ENV_OS,
                Globals::ENV_OS_INFO,
                Globals::ENV_RUNTIME_MODE,
                Globals::ENV_VERSION
            ))) {
                throw new \Exception("Can't set environment - $key", Globals::EXCEPTION_ERROR_ENVIRONMENT_SET_LIMIT);
            }

            if (Globals::ENV_MODE === $key && false === array_search($value, array(
                    Globals::MODE_DEV, Globals::MODE_PUB
                ))) {
                throw new \Exception("Mode must be " . Globals::MODE_DEV . " or " . Globals::MODE_PUB, Globals::EXCEPTION_ERROR_ENVIRONMENT);
            }
            $this->environment[$key] = $value;
        }
    }

    private function onError(AppModuleException $ex)
    {
        ExceptionManager::handle($ex);
    }
}