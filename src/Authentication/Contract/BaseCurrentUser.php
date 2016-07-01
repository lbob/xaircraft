<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 20:26
 */

namespace Xaircraft\Authentication\Contract;


use Xaircraft\Core\Container;

class BaseCurrentUser extends Container
{
    public $isExist = false;
    
    public function __construct(array $user)
    {
        foreach ($user as $key => $item) {
            $this[$key] = $item;
        }
        if(!empty($user)){
            $this->isExist = true;
        }
    }

    public static function create(array $user)
    {
        return new BaseCurrentUser($user);
    }

    public function __get($key)
    {
        if(isset($this[$key])){
            return $this[$key];
        }
        return null;
    }

    public function isExist()
    {
        return $this->isExist;
    }
}