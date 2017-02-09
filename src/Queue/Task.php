<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 11:06
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;
use Xaircraft\Exception\ExceptionHelper;

class Task
{
    const EVENT_BEFORE = 'before';
    const EVENT_RESOLVED = 'resolved';
    const EVENT_REJECTED = 'rejected';
    const EVENT_RESUME = 'resume';

    private $events = array();

    /**
     * @var TaskContext
     */
    private $context;

    public static function create(TaskContext $context)
    {
        $task = new Task();
        $task->context = $context;
        return $task;
    }

    public function before()
    {
        $this->context->getInstance()->onBefore($this->context);
        $this->dispatch(self::EVENT_BEFORE);
    }

    public function invoke()
    {
        $name = $this->context->getName();
        $method = $this->context->getMethod();
        if (class_exists($name)) {
            $instance = DI::get($name);
            if ($instance instanceof ITask) {
                $this->context->setInstance($instance);
                $this->before();
            }
        }
        try {
            if ($this->context->isResume()) {
                $this->resume();
                if ($this->context->isCancelResume()) { return; }
            }
            call_user_func_array(array($this->context->getInstance(), $method), array($this->context));
            $this->resolved();
        } catch (\Exception $ex) {
            $this->context->setException($ex);
            $this->rejected();
        }
    }

    public function resolved()
    {
        $this->context->getInstance()->onResolved($this->context);
        $this->dispatch(self::EVENT_RESOLVED);
    }

    public function rejected()
    {
        $this->context->getInstance()->onRejected($this->context);
        $this->dispatch(self::EVENT_REJECTED);
    }

    public function resume()
    {
        $result = $this->context->getInstance()->onResume($this->context);
        $this->context->setResume(true);
        $this->context->setCancelResume($result === false ? true : false);
        $this->dispatch(self::EVENT_RESUME, $result);
        return $result;
    }

    /**
     * @return TaskContext
     */
    public function getContext()
    {
        return $this->context;
    }

    public function addListener($eventname, callable $callback)
    {
        $this->events[$eventname][] = $callback;
    }

    private function dispatch($eventname, $params = null)
    {
        if (array_key_exists($eventname, $this->events) !== false) {
            $events = $this->events[$eventname];
            if (!empty($events)) {
                foreach ($events as $event) {
                    call_user_func($event, $params);
                }
            }
        }
    }
}