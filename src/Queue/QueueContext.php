<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 11:10
 */

namespace Xaircraft\Queue;


use Xaircraft\Core\Container;
use Xaircraft\DI;

class QueueContext extends Container
{
    const MODE_SYNC = 0;
    const MODE_ASYNC = 1;

    //运行实例
    private $implement;

    /**
     * @var QueueConfiguration
     */
    private $config;

    /**
     * @var Task
     */
    private $currentTask;

    /**
     * @var Task[]
     */
    private $resumeTasks;

    /**
     * QueueContext constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        //根据配置初始化上下文
        $this->config = new QueueConfiguration();
        $this->implement = $this->config->find('implement')->get();
    }

    /**
     * @return QueueContext
     */
    public static function getInstance()
    {
        return DI::get(QueueContext::class);
    }

    /**
     * @return QueueConfiguration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Task
     */
    public function getCurrentTask()
    {
        return $this->currentTask;
    }

    /**
     * @param Task $currentTask
     */
    public function setCurrentTask(Task $currentTask)
    {
        $this->currentTask = $currentTask;
    }

    /**
     * @return mixed
     */
    public function getImplement()
    {
        return !empty($this->implement) ? $this->implement : SyncQueueImpl::class;
    }

    /**
     * @return Task[]
     */
    public function getResumeTasks()
    {
        return $this->resumeTasks;
    }

    /**
     * @param Task $task
     */
    public function appendResumeTasks(Task $task)
    {
        $this->resumeTasks[] = $task;
    }
}