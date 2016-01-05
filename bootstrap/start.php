<?php

use \Xaircraft\App;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/11
 * Time: 18:29
 */

$app = App::instance();
$app->bindPaths(require_once __DIR__.'/paths.php');

$app->environment(\Xaircraft\Globals::ENV_MODE, \Xaircraft\Globals::MODE_DEV);

return $app;