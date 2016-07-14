<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2016/7/14
 * Time: 11:40
 */

namespace Xaircraft\Web;


abstract class HttpModule
{
    public abstract function start();

    public abstract function end();
}