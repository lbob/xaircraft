<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 17:18
 */

namespace Xaircraft\Web;


use Xaircraft\DI;
use Xaircraft\Web\Session\FileSessionProvider;
use Xaircraft\Web\Session\SessionProvider;

class Session
{
    /**
     * @var Session
     */
    private static $instance;

    /**
     * @var SessionProvider
     */
    private $provider;

    private function __construct(SessionProvider $provider)
    {
        $this->provider = $provider;
    }

    private static function instance()
    {
        if (!isset(self::$instance)) {
            $provider = DI::get(SessionProvider::class);
            if (!isset($provider)) {
                DI::bindSingleton(SessionProvider::class, new FileSessionProvider());
                $provider = DI::get(SessionProvider::class);
            }
            self::$instance = new Session($provider);
        }
        return self::$instance;
    }

    public static function put($key, $value)
    {
        self::instance()->provider->put($key, $value);
    }

    public static function push($key, $value)
    {
        self::instance()->provider->push($key, $value);
    }

    public static function get($key, $default = null)
    {
        return self::instance()->provider->get($key, $default);
    }

    public static function pull($key)
    {
        return self::instance()->provider->pull($key);
    }

    public static function all()
    {
        return self::instance()->provider->all();
    }

    public static function has($key)
    {
        return self::instance()->provider->has($key);
    }

    public static function forget($key)
    {
        self::instance()->provider->forget($key);
    }

    public static function flush()
    {
        self::instance()->provider->flush();
    }

    public static function regenerate()
    {
        return self::instance()->provider->regenerate();
    }

    public static function flash($key, $value)
    {
        return self::instance()->provider->flash($key, $value);
    }

    public static function reflash($key)
    {
        return self::instance()->provider->reflash($key);
    }

    public static function remeber($key)
    {
        return self::instance()->provider->remeber($key);
    }
}