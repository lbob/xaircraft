<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/9/29
 * Time: 11:01
 */

namespace Xaircraft\Extensions\WeChat\API\Contract;


use Xaircraft\Exception\ExceptionHelper;

class NewsArticle
{

    public $title;
    public $description;
    public $url;
    public $picurl;

    public function validate()
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($this->title, "title 不能为空");
    }
}