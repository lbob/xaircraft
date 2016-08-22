<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 20:26
 */

namespace Xaircraft\Authentication\Contract;


use Xaircraft\Core\Container;

class CurrentUser extends BaseCurrentUser
{
    public function __construct($id, $username, $name, $level, $email, $extra)
    {
        $this['id'] = $id;
        $this['username'] = $username;
        $this['name'] = $name;
        $this['level'] = $level;
        $this['email'] = $email;
        $this['extra'] = serialize($extra);
        parent::__construct((array)$this);
    }

    public static function create($id, $username, $name, $level, $email, $extra)
    {
        return new CurrentUser($id, $username, $name, $level, $email, $extra);
    }

    public function getID()
    {
        return $this['id'];
    }

    public function getUsername()
    {
        return $this['username'];
    }

    public function getName()
    {
        return $this['name'];
    }

    public function getLevel()
    {
        return $this['level'];
    }

    public function getEmail()
    {
        return $this['email'];
    }

    public function getExtra()
    {
        return unserialize($this['extra']);
    }
}