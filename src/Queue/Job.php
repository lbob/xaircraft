<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 11:12
 */

namespace Xaircraft\Queue;


use Xaircraft\Core\Strings;
use Xaircraft\Exception\ModelException;

class Job
{
    public $uid;

    public $key;

    public $name;

    public $method;

    public $params;

    public $cbUrl;

    const DEFAULT_FIRE_METHOD = 'fire';

    private function __construct($key, array $params, $cbUrl)
    {
        $this->uid = Strings::guid();
        $this->key = $key;
        $this->params = $params;
        $this->cbUrl = $cbUrl;
        $this->parseKey();
    }

    public static function create($key, array $params, $cbUrl = '')
    {
        return new Job($key, $params, $cbUrl);
    }

    private function parseKey()
    {
        $key = $this->key;

        if (!empty($key)) {
            if (stripos($key, '@') !== false) {
                $statements = explode('@', $key);
                $this->name = isset($statements[0]) ? $statements[0] : null;
            } else {
                $this->name = $key;
            }
            $this->method = isset($statements[1]) ? $statements[1] : self::DEFAULT_FIRE_METHOD;
        }

        if (empty($this->name)) {
            throw new \Exception('Job name empty.');
        }
    }
}