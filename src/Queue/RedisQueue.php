<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 19:13
 */

namespace Xaircraft\Queue;


use Predis\Client;
use Xaircraft\App;
use Xaircraft\Exception\ModelException;
use Xaircraft\Extensions\Log\Log;

class RedisQueue extends BaseQueue
{
    const REDIS_KEY = 'ASYNC_QUEUE';

    /**
     * @var Client
     */
    private $client;

    private $auth;

    public function __construct()
    {
        if (!file_exists(App::path('redis'))) {
            throw new \Exception('Redis config null.');
        }
        $configs = require_once App::path('redis');
        if (array_key_exists('queue', $configs) !== false) {
            $config = $configs['queue'];
        }
        if (empty($config)) {
            throw new \Exception('Redis config empty.');
        }
        $this->client = new Client($config);

        if (!empty($config['auth'])) {
            $this->auth = $config['auth'];
            $this->client->auth($this->auth);
        }
    }

    public function mode()
    {
        return BaseQueue::MODE_ASYNC;
    }

    public function push($job, array $params = null, $cbUrl = null)
    {
        $this->queue[] = Job::create($job, $params, $cbUrl);

        Log::debug('PUSH', $job, $params);

        return $this;
    }

    public function waitPopAll($timeout = 0)
    {
        while (true) {
            $values = $this->client->brpop(self::REDIS_KEY, $timeout);
            if (!isset($values)) {
                yield;
            } else {
                yield unserialize($values[1]);
            }
        }
    }

    public function commit()
    {
        if (!$this->isRollback) {
            if (!empty($this->queue)) {
                /** @var Job $item */
                foreach ($this->queue as $item) {
                    $this->client->lpush(self::REDIS_KEY, serialize($item));
                }

                if (!empty($this->commitHandles)) {
                    foreach ($this->commitHandles as $commitHandle) {
                        call_user_func($commitHandle);
                    }
                }
            } else {
                Log::debug('COMMIT_QUEUE_EMPTY', 'EMPTY');
            }
        }
    }

    public function rollback()
    {
        $this->isRollback = true;

        if (!empty($this->rollbackHandles)) {
            foreach ($this->rollbackHandles as $rollbackHandle) {
                call_user_func($rollbackHandle);
            }
        }
    }
}