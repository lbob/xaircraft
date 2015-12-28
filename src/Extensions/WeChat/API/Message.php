<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/9/29
 * Time: 10:59
 */

namespace Xaircraft\Extensions\WeChat\API;

use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\WeChat\Application;

class Message
{
    private $API = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={ACCESS_TOKEN}';
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function sendText($content, $toUsers = null, $toParties = null, $toTags = null, $isSafe = 0)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($content, "Content Can't be null");

        $toUsers = $this->formatParameter($toUsers);
        $toParties = $this->formatParameter($toParties);
        $toTags = $this->formatParameter($toTags);

        $body = array(
            'touser' => $toUsers,
            'toparty' => $toParties,
            'totag' => $toTags,
            'msgtype' => "text",
            'agentid' => $this->app->getAppID(),
            'text' => array(
                'content' => $content
            ),
            'safe' => $isSafe
        );

        $this->app->post($this->API, $this->app->formatBody($body));
    }

    public function sendNews(array $articles, $toUsers = null, $toParties = null, $toTags = null)
    {
        ExceptionHelper::ThrowIfNullOrEmpty($articles, "Articles Can't be null");

        $toUsers = $this->formatParameter($toUsers);
        $toParties = $this->formatParameter($toParties);
        $toTags = $this->formatParameter($toTags);

        foreach ($articles as $item) {
            $item->validate();
        }

        $body = array(
            'touser' => $toUsers,
            'toparty' => $toParties,
            'totag' => $toTags,
            'msgtype' => "news",
            'agentid' => $this->app->getAppID(),
            'news' => array(
                'articles' => $articles
            )
        );
        $this->app->post($this->API, $this->app->formatBody($body));
    }

    private function formatParameter($param)
    {
        if (is_array($param) && !empty($param)) {
            $param = implode('|', $param);
        } else if (is_string($param) && $param == "@all") {
            $param = "@all";
        } else {
            $param = "";
        }
        return $param;
    }
}