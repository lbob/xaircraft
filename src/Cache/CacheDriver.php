<?php

namespace Xaircraft\Cache;


/**
 * Class CacheProvider
 *
 * @package Xaircraft\Cache
 * @author lbob created at 2015/1/17 11:59
 */
interface CacheDriver {

    /**
     * 存储数据到缓存中
     * @param $key
     * @param $value
     * @param $minutes
     * @return mixed
     */
    public function put($key, $value, $minutes);

    /**
     * 检查缓存是否存在
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * 从缓存中取得数据，若数据为空则回传默认值
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * 永久存储数据到缓存中
     * @param $key
     * @param $value
     * @return mixed
     */
    public function forever($key, $value);

    /**
     * 从缓存中取得数据，当数据不存在时会存储默认值
     * @param $key
     * @param $minutes
     * @param $defaultValue
     * @return mixed
     */
    public function remember($key, $minutes, $defaultValue);

    /**
     * 从缓存中取得数据，当数据不存在时会永久存储默认值
     * @param $key
     * @param $defaultValue
     * @return mixed
     */
    public function rememberForever($key, $defaultValue);

    /**
     * 从缓存中取得数据并删除缓存
     * @param $key
     * @return mixed
     */
    public function pull($key);

    /**
     * 从缓存中删除数据
     * @param $key
     * @return mixed
     */
    public function forget($key);

    /**
     * 递增值
     * @param $key
     * @param int $amount
     * @return mixed
     */
    public function increment($key, $amount = 1);

    /**
     * 递减值
     * @param $key
     * @param int $amount
     * @return mixed
     */
    public function decrement($key, $amount = 1);
}

 