<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 17:17
 */

namespace Xaircraft\Web\Session;


/**
 * Class SessionProvider
 *
 * @package Xaircraft\Session
 * @author skyweo created at 14/12/17 19:39
 */
interface SessionProvider
{
    public function put($key, $value);
    public function push($key, $value);
    public function get($key, $default = null);
    public function pull($key);
    public function all();
    public function has($key);
    public function forget($key);
    public function flush();
    public function regenerate();
    public function flash($key, $value);
    public function reflash($key);
    public function remeber($key);
}
