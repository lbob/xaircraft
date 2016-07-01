<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 21:16
 */

namespace Xaircraft\Authentication;


use Xaircraft\Authentication\Contract\BaseCurrentUser;
use Xaircraft\DI;

class Auth
{
    public static function check()
    {
        /** @var BaseCurrentUser $user */
        $user = self::getAuthStorage()->get();
        if (!isset($user) || $user->isExist()) {
            return false;
        }
        return true;
    }

    public static function logout()
    {
        self::getAuthStorage()->clear();
    }

    public static function user()
    {
        $user = self::getAuthStorage()->get();
        return $user;
    }

    /**
     * @return AuthStorage
     */
    private static function getAuthStorage()
    {
        return DI::get(AuthStorage::class);
    }
}