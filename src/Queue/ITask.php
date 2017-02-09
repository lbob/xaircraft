<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 11:01
 */

namespace Xaircraft\Queue;


interface ITask
{
    public function onBefore(TaskContext $context);

    public function onResolved(TaskContext $context);

    public function onRejected(TaskContext $context);

    public function onResume(TaskContext $context);
}