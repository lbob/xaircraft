<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 17:37
 */

namespace Xaircraft\Web\Session;


/**
 * Class FileSessionProvider
 *
 * @package Xaircraft\Session
 * @author skyweo created at 14/12/17 19:41
 */
class FileSessionProvider implements SessionProvider
{
    const FLASH = "__flash";
    const RECYCLE = '__recycle';

    public function __construct()
    {
        session_start();

        $this->flashInit();
    }

    private function flashInit()
    {
        $recycle = $this->get(self::RECYCLE, array());
        foreach ($recycle as $item) {
            $this->forget($item);
        }
        $this->forget(self::RECYCLE);
        $flash = $this->get(self::FLASH, array());
        foreach ($flash as $key => $value) {
            $this->put($key, $value);
            $this->push(self::RECYCLE, $key);
        }
        $this->forget(self::FLASH);
    }

    public function put($key, $value)
    {
        if (isset($key)) {
            $_SESSION[$key] = $value;
        }
    }

    public function push($key, $value)
    {
        if (isset($key)) {
            $_SESSION[$key][] = $value;
        }
    }

    public function get($key, $default = null)
    {
        if (isset($key) && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else if (isset($default)) {
            if (is_callable($default)) {
                return call_user_func($default);
            } else {
                return $default;
            }
        }
        return null;
    }

    public function pull($key)
    {
        if (isset($key)) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }

    public function all()
    {
        return $_SESSION;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function forget($key)
    {
        if (isset($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function flush()
    {
        unset($_SESSION);
    }

    public function regenerate()
    {
        return session_regenerate_id();
    }

    public function flash($key, $value)
    {
        $flash = $this->get(self::FLASH, array());
        $flash[$key] = $value;
        $this->put(self::FLASH, $flash);
    }

    public function reflash($key)
    {
        $this->cancelRecycle($key);
    }

    public function remeber($key)
    {
        $this->cancelRecycle($key, true);
    }

    private function cancelRecycle($key, $isRemeber = false)
    {
        if (isset($key)) {
            $recycle = $this->get(self::RECYCLE, array());
            $index = 0;
            foreach ($recycle as $item) {
                if ($key == $item) {
                    unset($recycle[$index]);
                }
                $index++;
            }
            $this->put(self::RECYCLE, $recycle);
            if (!$isRemeber) {
                $value = $this->get($key);
                $this->flash($key, $value);
            }
        }
    }
}

