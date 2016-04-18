<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/27
 * Time: 10:32
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\App;
use Xaircraft\Console\Process;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Exception\DaemonException;

abstract class Daemon extends Worker
{
    protected $singleton = true;
    protected $args;
    private $sync;

    public function __construct(array $args, $sync = false)
    {
        parent::__construct($args);
        $this->args = $args;
        $this->sync = $sync;
    }

    public function start()
    {
        WorkerProcessContainer::run(array($this), !$this->sync);
    }

    public function getName()
    {
        return $this->name;
    }
}