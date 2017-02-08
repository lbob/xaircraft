<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/8
 * Time: 10:02
 */

namespace Xaircraft\Queue;


interface IJob
{
    public function onResolved(Job $job);

    public function onRejected(Job $job, \Exception $ex = null);
}