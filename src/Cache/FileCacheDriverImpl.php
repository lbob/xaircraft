<?php

namespace Xaircraft\Cache;
use Carbon\Carbon;
use Xaircraft\App;
use Xaircraft\Common\IO;
use Xaircraft\Core\IO\Directory;
use Xaircraft\Exception\ExceptionHelper;


/**
 * Class FileCacheDriverImpl
 *
 * @package Xaircraft\Cache
 * @author lbob created at 2015/4/28 11:56
 */
class FileCacheDriverImpl implements CacheDriver {

    const EXPIRED_FOREVER = -1;

    private $cacheFolder = '/files';

    /**
     * @param $key
     * @return string
     */
    private function getPath($key)
    {
        $fileName = md5($key);
        $folder = App::path('cache') . $this->cacheFolder;
        $path = $folder . '/' . $fileName . '.dat';
        Directory::makeDir($folder);
        return $path;
    }

    /**
     * @param FileCacheItem $value
     * @throws \Exception
     */
    private function write(FileCacheItem $value)
    {
        if (!isset($value)) {
            throw new \Exception("Invalid value.");
        }

        $path = $this->getPath($value->key);

        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, serialize($value), LOCK_EX);
    }

    /**
     * @param $key
     * @return FileCacheItem|null
     */
    private function read($key)
    {
        $path = $this->getPath($key);
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $value = unserialize($content);
            if ($value instanceof FileCacheItem) {
                if ($value->expired === self::EXPIRED_FOREVER || $value->expired > time()) {
                    return $value;
                }
            }
        }
        return null;
    }

    private function delete($key)
    {
        $path = $this->getPath($key);
        unlink($path);
    }

    /**
     * 存储数据到缓存中
     * @param $key
     * @param $value
     * @param $minutes
     * @return mixed
     */
    public function put($key, $value, $minutes)
    {
        $item = new FileCacheItem();
        $item->key = $key;
        $item->value = $value;
        $item->expired = Carbon::now()->addMinutes($minutes)->getTimestamp();
        $this->write($item);
    }

    /**
     * 检查缓存是否存在
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        $value = $this->read($key);
        return isset($value);
    }

    /**
     * 从缓存中取得数据，若数据为空则回传默认值
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->read($key);
        return isset($value) ? $value->value : (is_callable($default) ? call_user_func($default) : $default);
    }

    /**
     * 永久存储数据到缓存中
     * @param $key
     * @param $value
     * @return mixed
     */
    public function forever($key, $value)
    {
        $item = new FileCacheItem();
        $item->key = $key;
        $item->value = $value;
        $item->expired = self::EXPIRED_FOREVER;
        $this->write($item);
    }

    /**
     * 从缓存中取得数据，当数据不存在时会存储默认值
     * @param $key
     * @param $minutes
     * @param $defaultValue
     * @return mixed
     */
    public function remember($key, $minutes, $defaultValue)
    {
        $value = $this->read($key);
        if (isset($value) && isset($value->value)) {
            return $value->value;
        } else {
            $defaultValue = is_callable($defaultValue) ? call_user_func($defaultValue) : $defaultValue;
            $this->put($key, $defaultValue, $minutes);
            return $defaultValue;
        }
    }

    /**
     * 从缓存中取得数据，当数据不存在时会永久存储默认值
     * @param $key
     * @param $defaultValue
     * @return mixed
     */
    public function rememberForever($key, $defaultValue)
    {
        $value = $this->read($key);
        if (isset($value) && isset($value->value)) {
            return $value->value;
        } else {
            $defaultValue = is_callable($defaultValue) ? call_user_func($defaultValue) : $defaultValue;
            $this->forever($key, $defaultValue);
            return $defaultValue;
        }
    }

    /**
     * 从缓存中取得数据并删除缓存
     * @param $key
     * @return mixed
     */
    public function pull($key)
    {
        $value = $this->read($key);
        $this->delete($key);
        return isset($value) ? $value->value : null;
    }

    /**
     * 从缓存中删除数据
     * @param $key
     * @return mixed
     */
    public function forget($key)
    {
        $this->delete($key);
    }

    /**
     * 递增值
     * @param $key
     * @param int $amount
     * @return mixed|void
     * @throws \Exception
     */
    public function increment($key, $amount = 1)
    {
        throw new \Exception("UnImplement increment() method");
    }

    /**
     * 递减值
     * @param $key
     * @param int $amount
     * @return mixed|void
     * @throws \Exception
     */
    public function decrement($key, $amount = 1)
    {
        throw new \Exception("UnImplement decrement() method");
    }
}

 