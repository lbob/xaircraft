<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/9
 * Time: 19:50
 */

namespace Xaircraft\Authentication;


use Xaircraft\Authentication\Contract\CurrentUser;
use Xaircraft\Web\Session;

class SessionAuthStorage implements AuthStorage
{
    private $currentUserSessionID = '__current_user__';

    public function set(CurrentUser $user)
    {
        Session::put($this->currentUserSessionID, $user);
    }

    public function get()
    {
        return Session::get($this->currentUserSessionID);
    }

    public function clear()
    {
        Session::forget($this->currentUserSessionID);
    }
}