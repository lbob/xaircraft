<?php

namespace Xaircraft\Extensions\WeChat;
use Xaircraft\App;
use Xaircraft\Cache\CacheDriver;
use Xaircraft\Configuration\Settings;


/**
 * Class Corporation
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 15:23
 */
abstract class Corporation
{

    private $key = 'CORP_KEY_';

    private $corpid;

    private $corpsecret;

    /**
     * @var CacheDriver
     */
    private $cacheDriver;

    /**
     * @var CorpInfo
     */
    private $corpInfo;

    public function __construct(CacheDriver $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

    public abstract function getCorpName();

    public abstract function getCorpSecretConfig();

    public final function getAccessToken()
    {
        $corpName = $this->getCorpName();
        if (!isset($corpName) || $corpName == '') {
            throw new \Exception("Invalid CorpName.");
        }
        if ($this->cacheDriver->has($this->key . $this->getCorpName())) {
            $this->corpInfo = unserialize($this->cacheDriver->get($this->key . $this->getCorpName()));
        }
        if (!isset($this->corpInfo) || $this->corpInfo->expired()) {
            $this->corpInfo = $this->generateAccessToken();
            $this->cacheDriver->put($this->key . $this->getCorpName(), serialize($this->corpInfo), $this->corpInfo->expires_in);
        }
        if (isset($this->corpInfo)) {
            return $this->corpInfo->access_token;
        }
        throw new \Exception("WeChat: 无法获取access_token");
    }

    public final function option($key)
    {
        $configs = Settings::load('wechat');
        if (!isset($configs) || empty($configs) || !array_key_exists($this->getCorpName(), $configs)) {
            throw new \Exception("缺少微信配置信息");
        }
        $config = $configs[$this->getCorpName()];
        if (!isset($config) || empty($config)) {
            throw new \Exception("缺少微信配置信息：" . $this->getCorpName());
        }

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }
        return null;
    }

    private function generateAccessToken()
    {
        $config = $this->getCorpSecretConfig();

        if (!isset($config) || empty($config)) {
            $config['corpid'] = $this->option('corpid');
            $config['corpsecret'] = $this->option('corpsecret');
            if (!array_key_exists('corpid', $config) || !array_key_exists('corpsecret', $config)) {
                throw new \Exception("缺少微信配置信息：" . $this->getCorpName());
            }
        }

        $this->corpid = $config['corpid'];
        $this->corpsecret = $config['corpsecret'];

        $result = Request::get(API::GET_ACCESS_TOKEN, array(
            'corpid' => $this->corpid,
            'corpsecret' => $this->corpsecret
        ));

        $corpInfo = new CorpInfo();
        $corpInfo->expires_in = $result['expires_in'];
        $corpInfo->access_token = $result['access_token'];
        $corpInfo->create_at = time();
        return $corpInfo;
    }
}

