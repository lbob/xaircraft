<?php

namespace Xaircraft\Extensions\WeChat;
use Carbon\Carbon;


/**
 * Class JsApiTicket
 *
 * @package WeChat
 * @author Polaris created at 2015/5/18 16:18
 */
class JsApiTicket {

    public $noncestr;
    public $timestamp;
    public $jsApiTicket;

    public $expiredSeconds;

    public function expired()
    {
        return ($this->timestamp + $this->expiredSeconds) <= time() || !isset($this->jsApiTicket);
    }
}

 