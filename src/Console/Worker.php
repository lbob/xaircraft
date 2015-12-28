<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/24
 * Time: 18:50
 */

namespace Xaircraft\Console;


interface Worker
{
    public function beforeStart();

    public function handle();
}