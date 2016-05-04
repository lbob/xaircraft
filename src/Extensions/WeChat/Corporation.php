<?php

namespace Xaircraft\Extensions\WeChat;
use Xaircraft\Cache\CacheDriver;
use Xaircraft\Configuration\Settings;
use Xaircraft\Exception\ExceptionHelper;


/**
 * Class Corporation
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 15:23
 */
abstract class Corporation
{

    private $key = 'CORP_KEY_';
    private $ticketKey = 'TICKET_KEY_';

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

    /**
     * @var JsApiTicket
     */
    private $ticketInfo;

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

    public final function getWeChatSignatureInfo($url)
    {
        ExceptionHelper::ThrowIfNullOrEmpty($url, '缺少url');
        ExceptionHelper::ThrowIfNullOrEmpty($this->getCorpName(), "Invalid CorpName.");

        if ($this->cacheDriver->has($this->ticketKey . $this->getCorpName())) {
            $this->ticketInfo = unserialize($this->cacheDriver->get($this->ticketKey . $this->getCorpName()));
        }
        $signature = $this->getWeChatSignature($url);

        return array(
            'appId' => $this->option('corpid'),
            'timestamp' => $this->ticketInfo->timestamp,
            'nonceStr' => $this->ticketInfo->noncestr,
            'signature' => $signature
        );
    }

    private function getWeChatSignature($url)
    {
        if (!isset($this->ticketInfo) || (isset($this->ticketInfo) && $this->ticketInfo->expired())) {
            $this->generateJsApiTicket();
        }

        $signatureString = "jsapi_ticket={$this->ticketInfo->jsApiTicket}";
        $signatureString .= "&noncestr={$this->ticketInfo->noncestr}";
        $signatureString .= "&timestamp={$this->ticketInfo->timestamp}";
        $signatureString .= "&url={$url}";

        return sha1($signatureString);
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

    private function generateJsApiTicket()
    {
        $content = Request::get(API::GET_JS_API_TICKET, array('ACCESS_TOKEN' => $this->getAccessToken()));
        $this->ticketInfo = new JsApiTicket();
        $this->ticketInfo->noncestr = $this->getNoncestr();
        $this->ticketInfo->timestamp = time();
        $this->ticketInfo->jsApiTicket = $content['ticket'];
        $this->ticketInfo->expiredSeconds = $content['expires_in'] - 200;//提前200秒刷新ticket

        $this->cacheDriver->put($this->ticketKey . $this->getCorpName(), serialize($this->ticketInfo), $this->ticketInfo->expiredSeconds);
    }

    private function getNoncestr($length = 8)
    {
        $str = null;
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }

        return $str;
    }
}

