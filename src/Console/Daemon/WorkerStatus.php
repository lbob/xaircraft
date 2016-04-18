<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/18
 * Time: 15:12
 */

namespace Xaircraft\Console\Daemon;


class WorkerStatus
{
    public $pid;

    public $process_count;

    public $shutdown_process_count;

    public $status;

    public $start_at;
}