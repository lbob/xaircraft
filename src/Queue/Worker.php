<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 15:20
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;
use Xaircraft\Exception\ExceptionHelper;

class Worker
{
    private $task;

    private function __construct(Task $task)
    {
        $this->task = $task;
    }

    public static function create($command, array $params, $uid = '', $status = TaskContext::STATUS_NOSTART)
    {
        list($name, $method) = self::parseCommand($command);
        $context = TaskContext::create($name, $method, $params, $status, $uid);
        $task = Task::create($context);

        return new Worker($task);
    }

    public static function createFromTask(Task $task)
    {
        return new Worker($task);
    }

    public function run()
    {
        /** @var QueueContext $context */
        $context = DI::get(QueueContext::class);
        $context->setCurrentTask($this->task);
        $this->task->addListener(Task::EVENT_BEFORE, function () use ($context) { QueueEvents::onTaskBeforeFire($context); });
        $this->task->addListener(Task::EVENT_RESOLVED, function () use ($context) { QueueEvents::onTaskResolved($context); });
        $this->task->addListener(Task::EVENT_REJECTED, function () use ($context) { QueueEvents::onTaskRejected($context); });
        $this->task->addListener(Task::EVENT_RESUME, function () use ($context) { QueueEvents::onTaskResume($context); });
        $this->task->invoke();
    }

    private static function parseCommand($command)
    {
        ExceptionHelper::ThrowIfNullOrEmpty($command, 'Task command cannot be null.');
        $sections = explode('@', $command);
        $name = isset($sections[0]) ? $sections[0] : null;
        $method = isset($sections[1]) ? $sections[1] : 'fire';
        ExceptionHelper::ThrowIfNullOrEmpty($name, 'Task name cannot be null.');

        return array($name, $method);
    }
}