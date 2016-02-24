<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 15:10
 */

namespace Xaircraft\Web\Http;


use Xaircraft\Core\Collections\Generic;
use Xaircraft\Core\Strings;
use Xaircraft\Web\Http\RequestFileInfo;

class Request
{
    private $params = array();

    private $headers = array();

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function param($key, $htmlFilter = true)
    {
        if (array_key_exists($key, $this->params)) {
            if (!$htmlFilter) {
                return $this->params[$key];
            }
            return Strings::htmlFilter($this->params[$key]);
        }
        return null;
    }

    public function params($htmlFilter = true)
    {
        $params = array();
        foreach ($this->params as $key => $value) {
            if (!$htmlFilter) {
                $params[$key] = $value;
            } else {
                $params[$key] = Strings::htmlFilter($value);
            }
        }
        return $params;
    }

    public function post($key, $htmlFilter = true)
    {
        if (isset($key) && isset($_POST[$key])) {
            if (!$htmlFilter) {
                return $_POST[$key];
            }
            return Strings::htmlFilter($_POST[$key]);
        }
        return null;
    }

    public function posts($prefix = null, $htmlFilter = true)
    {
        if (isset($prefix) && is_string($prefix)) {
            $posts = array();
            $items = Generic::fast_array_key_filter($_POST, $prefix . '_');
            foreach ($items as $key => $value) {
                $key         = str_replace($prefix . '_', '', $key);
                if (!$htmlFilter) {
                    $posts[$key] = $value;
                } else {
                    $posts[$key] = Strings::htmlFilter($value);
                }
            }
            return $posts;
        } else {
            $posts = array();
            foreach ($_POST as $key => $value) {
                if (!$htmlFilter) {
                    $posts[$key] = $value;
                } else {
                    $posts[$key] = Strings::htmlFilter($value);
                }
            }

            return $posts;
        }
    }

    public function header($key)
    {
        if (empty($this->headers)) {
            foreach (getallheaders() as $item => $value) {
                $this->headers[$item] = $value;
            }
        }

        if (empty($key) || '' === $key) {
            return null;
        }

        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
        return null;
    }

    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }

    public function isXMLHttpRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function isPJAX()
    {
        $pjax = $this->param('_pjax');
        return ((isset($_SERVER['HTTP_X_PJAX']) && strtolower($_SERVER['HTTP_X_PJAX']) === 'true') || isset($pjax));
    }

    public function files($keyword = null)
    {
        if (isset($_FILES)) {
            $files = array();
            foreach ($_FILES as $formKeyword => $filesMeta) {
                if (isset($keyword) && !strtolower($keyword) === strtolower($formKeyword)) {
                    continue;
                }
                $isArr = is_array($filesMeta['name']);
                $fileCount = count($filesMeta['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileInfo = new RequestFileInfo();
                    $fileInfo->name     = $isArr ? $filesMeta['name'][$i] : $filesMeta['name'];
                    $fileInfo->type     = $isArr ? $filesMeta['type'][$i] : $filesMeta['type'];
                    $fileInfo->tmp_name = $isArr ? $filesMeta['tmp_name'][$i] : $filesMeta['tmp_name'];
                    $fileInfo->error    = $isArr ? $filesMeta['error'][$i] : $filesMeta['error'];
                    $fileInfo->size     = $isArr ? $filesMeta['size'][$i] : $filesMeta['size'];
                    $files[] = $fileInfo;
                }
            }
            return $files;
        }
    }
}