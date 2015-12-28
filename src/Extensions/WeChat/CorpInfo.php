<?php

namespace Xaircraft\Extensions\WeChat;


/**
 * Class CorpInfo
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 16:24
 */
class CorpInfo {

    public $access_token;

    public $expires_in;

    public $create_at;

    /**
     * @return bool
     */
    public function expired()
    {
        if (time() >= ($this->create_at + $this->expires_in)) {
            return true;
        }
        return false;
    }
}

