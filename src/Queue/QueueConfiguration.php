<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 11:24
 */

namespace Xaircraft\Queue;


use Xaircraft\Configuration\Settings;
use Xaircraft\Database\Paging;

class QueueConfiguration
{

    private $configs;

    private $queryPaths;

    public function __construct()
    {
        $this->configs = Settings::load('queue');
        $this->clear();
    }

    private function clear()
    {
        $this->queryPaths = array();
    }

    public function find($key)
    {
        $this->queryPaths[] = $key;

        return $this;
    }

    public function get()
    {
        $configs = $this->configs;
        $result = $configs;

        if (!empty($configs)) {
            if (!empty($this->queryPaths)) {
                if (!is_array($configs)) {
                    $result = null;
                } else {
                    $result = null;
                    foreach ($this->queryPaths as $key) {
                        if (array_key_exists($key, $configs) !== false) {
                            $value = $configs[$key];
                            if (!empty($value) && is_array($value)) {
                                $configs = $value;
                                $result = $value;
                                continue;
                            } else {
                                $result = $value;
                            }
                        } else {
                            $result = null;
                        }
                    }
                }
            }
        }
        $this->clear();
        return $result;
    }
}