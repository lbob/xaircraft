<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/9
 * Time: 19:54
 */

namespace Xaircraft\Authentication;


use Xaircraft\Authentication\Contract\CurrentUser;
use Xaircraft\Cache\CacheDriver;

class CacheAuthStorage implements AuthStorage
{
    private $currentUserSessionID = '__current_user__';

    /**
     * @var CacheDriver
     */
    private $cacheDriver;

    public function __construct(CacheDriver $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

    public function set(CurrentUser $user)
    {
        $this->cacheDriver->put($this->currentUserSessionID, $user, 20);
    }

    public function get()
    {
        $this->cacheDriver->get($this->currentUserSessionID);
    }

    public function clear()
    {
        $this->cacheDriver->forget($this->currentUserSessionID);
    }
}