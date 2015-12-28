<?php

namespace Xaircraft\Extensions\WeChat;


/**
 * Class Application
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 17:00
 */
abstract class Application {

    /**
     * @var Corporation
     */
    private $corporation;

    public function __construct()
    {
        $this->corporation = $this->getCorporation();

        if (!isset($this->corporation)) {
            throw new \Exception("缺少Corporation对象");
        }
    }

    public abstract function getCorporation();

    public abstract function getAppID();

    public final function get($url, array $params = array())
    {
        $params['ACCESS_TOKEN'] = $this->corporation->getAccessToken();
        return Request::get($url, $params);
    }

    public final function post($url, $body, array $params = array())
    {
        $params['ACCESS_TOKEN'] = $this->corporation->getAccessToken();
        return Request::post($url, $body, $params);
    }

    public final function request($url, array $params = array())
    {
        $params['ACCESS_TOKEN'] = $this->corporation->getAccessToken();
        return Request::request($url, $params);
    }

    public final function formatBody(array $body = array())
    {
        return json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    public final function option($key)
    {
        return $this->corporation->option($key);
    }
}

