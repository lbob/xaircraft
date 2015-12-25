<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/24
 * Time: 10:14
 */

namespace Xaircraft\Console;


use Xaircraft\App;
use Xaircraft\Exception\ConsoleException;

class Process
{
    private $pid;

    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    public function stop()
    {
        return posix_kill($this->pid, SIGKILL);
    }

    public function getPID()
    {
        return $this->pid;
    }

    public static function fork($target)
    {
        if (!is_callable($target) && !$target instanceof Worker) {
            throw new ConsoleException("Must be callable or Worker.");
        }

        $pid = pcntl_fork();

        if (0 === $pid) {
            try {
                if ($target instanceof Worker) {
                    if (false !== $target->beforeStart()) {
                        $target->handle();
                    }
                } else {
                    call_user_func($target);
                }
            } catch (\Exception $ex) {
                throw new ConsoleException($ex->getMessage(), $ex);
            }
            App::end();
        }
        if (0 > $pid) {
            throw new ConsoleException("Process start failure.");
        }

        $process = new Process($pid);
        return $process;
    }

    public static function registerSignalHandler($closure, array $signals)
    {
        if (!empty($signals)) {
            foreach ($signals as $item) {
                pcntl_signal($item, $closure, false);
            }
        }
    }
}