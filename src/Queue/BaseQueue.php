<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 13:22
 */

namespace Xaircraft\Queue;


abstract class BaseQueue
{
    private $rollback = false;

    private $items;

    public function __construct()
    {
        $this->items = array();
    }

    public function push($command, array $params = array())
    {
        $this->items[] = new QueueItem($command, $params);
    }

    public abstract function waitPopAll($timeout = 0);

    public abstract function onCommit();

    public function commit()
    {
        if (!$this->isRollback()) {
            $this->onCommit();
            $this->items = array();
        }
    }

    public function rollback()
    {
        $this->rollback = true;
    }

    /**
     * @return boolean
     */
    public function isRollback()
    {
        return $this->rollback;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}