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
use Xaircraft\Exception\WebException;

return array(
    WebException::class => function ($ex) {
        echo 'asdf';
    },
    HttpAuthenticationException::class => function ($ex) {
        var_dump("aa");
    },
    DaemonException::class => function ($ex) {
        $path = \Xaircraft\App::path('log') . "/daemon/" . date("Ymd", time()) . '.log';
        File::appendText($path, $ex->getMessage);
    }
);