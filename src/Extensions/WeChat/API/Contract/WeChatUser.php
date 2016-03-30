<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/9/28
 * Time: 16:22
 */

namespace Xaircraft\Extensions\WeChat\API\Contract;


use Xaircraft\Exception\ExceptionHelper;

class WeChatUser
{

    public $userid;
    public $name;
    public $department;
    public $position;
    public $mobile;
    public $email;
    public $weixinid;
    public $extattr;
    public $enable;

    public function validate()
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($this->userid, "userid不能为空");
    }
}