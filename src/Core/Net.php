<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/28
 * Time: 9:53
 */

namespace Xaircraft\Core;


class Net {

    public static function getClientIP()
    {
        if(getenv('HTTP_CLIENT_IP')){
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }

    public static function getServerIP()
    {
        if (isset($_SERVER)) {
            if($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }

    public static function get($url, array $params = null, $header = false, array $headers = null)
    {
        $url = self::formatUrl($url, $params);
        return self::getRequestResult($url, null, $header, $headers);
    }

    public static function post($url, $body, array $params = null, $header = false, array $headers = null)
    {
        $url = self::formatUrl($url, $params);
        return self::getRequestResult($url, $body, $header, $headers);
    }

    private static function formatUrl($url, array $params = null)
    {
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }
        }

        return $url;
    }

    private static function getRequestResult($url, $postBody = null, $header = false, array $headers = null)
    {
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, '3');
            curl_setopt($ch, CURLOPT_TIMEOUT, '60');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (!empty($headers)) {
                curl_setopt ($ch, CURLOPT_HTTPHEADER , $headers);
            }
            if(!empty($postBody)){
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
            }
            if($header){
                curl_setopt($ch, CURLOPT_HEADER, 1);
            }
            $res = curl_exec($ch);
            curl_close($ch);
        } else if (function_exists('file_get_contents')) {
            if (isset($postBody)) {
                $opts = array(
                    'http' => array(
                        'method'  => 'POST',
                        'content' => $postBody,
                        'timeout' => 60
                    )
                );
                $context = stream_context_create($opts);
                $res = file_get_contents($url, false, $context);
            } else {
                $res = file_get_contents($url);
            }
        } else {
            throw new \Exception("getRequestResult失败");
        }
        return $res;
    }
}

