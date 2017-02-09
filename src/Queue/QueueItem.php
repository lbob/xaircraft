<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 17:38
 */

namespace Xaircraft\Queue;


class QueueItem
{
    public $command;

    public $params;

    public function __construct($command, $params)
    {
        $this->command = $command;
        $this->params = $params;
    }
}