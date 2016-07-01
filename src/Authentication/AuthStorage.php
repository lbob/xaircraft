<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/7
 * Time: 17:23
 */

namespace Xaircraft\Authentication;


use Xaircraft\Authentication\Contract\BaseCurrentUser;
use Xaircraft\Web\Session;

interface AuthStorage
{
    public function set(BaseCurrentUser $user);

    /**
     * @return BaseCurrentUser
     */
    public function get();

    public function clear();
}