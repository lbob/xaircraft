<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 18:01
 */

namespace Xaircraft\Queue;


use Predis\Client;
use Xaircraft\DI;

class RedisQueueImpl extends BaseQueue
{
    private $redisKey = 'task_queue_redis_key';

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        parent::__construct();

        $config = QueueContext::getInstance()->getConfig()->find('redis')->get();
        $auth = QueueContext::getInstance()->getConfig()->find('redis')->find('auth')->get();

        $this->client = new Client($config);
        if (!empty($auth)) {
            $this->client->auth($auth);
        }
    }

    public function waitPopAll($timeout = 0)
    {
        while (true) {
            $values = $this->client->brpop($this->redisKey, $timeout);
            if (!isset($values)) {
                yield;
            } else {
                yield unserialize($values[1]);
            }
        }
    }

    public function onCommit()
    {
        if (!empty($this->getItems())) {
            /** @var QueueItem $item */
            foreach ($this->getItems() as $item) {
                $this->client->lpush($this->redisKey, serialize($item));
            }
        }
    }
}