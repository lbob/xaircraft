<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 11:01
 */

namespace Xaircraft\Queue;


use Xaircraft\Core\Container;
use Xaircraft\Core\Strings;

class TaskContext extends Container
{
    const STATUS_NOSTART = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;

    private $uid;

    private $name;

    private $method;

    private $params;

    private $status;

    private $resume = false;

    private $cancelResume = false;

    /**
     * @var ITask
     */
    private $instance;

    /**
     * @var \Exception
     */
    private $exception;

    private function __construct() { }

    public static function create($name, $method, array $params, $status = self::STATUS_NOSTART, $uid = '')
    {
        $context = new TaskContext();
        $context->uid = empty($uid) ? Strings::guid() : $uid;
        $context->name = $name;
        $context->method = $method;
        $context->params = $params;
        $context->status = $status;

        return $context;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return ITask
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param ITask $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return boolean
     */
    public function isResume()
    {
        return $this->resume;
    }

    /**
     * @param boolean $resume
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    }

    /**
     * @return boolean
     */
    public function isCancelResume()
    {
        return $this->cancelResume;
    }

    /**
     * @param boolean $cancelResume
     */
    public function setCancelResume($cancelResume)
    {
        $this->cancelResume = $cancelResume;
    }
}