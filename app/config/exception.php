<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/8
 * Time: 14:25
 */

use Xaircraft\Core\IO\File;
use Xaircraft\Exception\DaemonException;
use Xaircraft\Exception\HttpAuthenticationException;

return array(
    HttpAuthenticationException::class => function ($ex) {
        var_dump("aa");
    },
    DaemonException::class => function ($ex) {
        $path = \Xaircraft\App::path('log') . "/daemon/" . date("Ymd", time()) . '.log';
        File::appendText($path, $ex->getMessage);
    }
);