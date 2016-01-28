<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16-1-28
 * Time: 下午3:57
 */

namespace Xaircraft\Extensions\Log;


use Xaircraft\Module\AppModule;

class LogAppModule extends AppModule
{

    public function appStart()
    {
        register_shutdown_function(array($this, 'shutdownFunction'));
    }

    public function handle()
    {
        // TODO: Implement handle() method.
    }

    public function appEnd()
    {
        // TODO: Implement appEnd() method.
    }

    public function shutdownFunction()
    {
        $e = error_get_last();
        if (isset($e)) {
            Log::emergency('SHUTDOWN_FUNCTION', $e['message'], $e);
        }
    }
}