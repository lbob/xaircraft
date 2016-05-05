<?php
/**
 * Created by PhpStorm.
 * User: Polaris
 * Date: 2015/5/5
 * Time: 11:37
 */

namespace Xaircraft\Extensions\WeChat\API;


use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\WeChat\API;
use Xaircraft\Extensions\WeChat\Application;

class Tag
{
    /**
     * @var Application
     */
    private $app;
    private $rootDeptId;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->rootDeptId = $app->option('rootDeptID');
    }

    /**
     * 创建标签
     * @param $tagName
     * @return
     */
    public function create($tagName)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($tagName, '缺少tagName');
        $postBody = array(
            'tagname' => $tagName
        );
        return $this->app->post(API::TAG_CREATE, $this->app->formatBody($postBody));
    }

    /**
     * 更新标签名字
     * @param $tagId
     * @param $tagName
     * @return mixed
     * @throws \Exception
     */
    public function update($tagId, $tagName)
    {
        ExceptionHelper::ThrowIfNotID($tagId, '缺少tagId');
        ExceptionHelper::ThrowIfSpaceOrEmpty($tagName, '缺少tagName');
        $postBody = array(
            'tagid' => $tagId,
            'tagname' => $tagName
        );
        return $this->app->post(API::TAG_UPDATE, $this->app->formatBody($postBody));
    }

    /**
     * 删除标签
     * @param $tagId
     * @throws \Exception
     */
    public function delete($tagId)
    {
        ExceptionHelper::ThrowIfNotID($tagId, '缺少tagId');
        return $this->app->get(API::TAG_DELETE, array('tagid' => $tagId));
    }

    /**
     * 获取标签成员
     * @param $tagId
     * @throws \Exception
     */
    public function get($tagId)
    {
        ExceptionHelper::ThrowIfNotID($tagId, '缺少tagId');
        return $this->app->get(API::TAG_GET, array('tagid' => $tagId));
    }

    /**
     * 增加标签成员
     * @param $tagId
     * @param array|null $userList
     * @param array|null $partyList
     * @return mixed
     * @throws \Exception
     */
    public function addTagUser($tagId, array $userList = null, array $partyList = null)
    {
        ExceptionHelper::ThrowIfNotID($tagId, '缺少tagId');
        ExceptionHelper::ThrowIfNotTrue($userList ? is_array($userList) : true, 'userList格式不正确');
        ExceptionHelper::ThrowIfNotTrue($partyList ? is_array($partyList) : true, 'partyList格式不正确');
        ExceptionHelper::ThrowIfNotTrue(!empty($userList) || !empty($partyList), 'userList、partyList不能同时为空');
        $postBody = array(
            'tagid' => $tagId,
            'userlist' => $userList,
            'partylist' => $partyList
        );
        return $this->app->post(API::TAG_ADD_USERS, $this->app->formatBody($postBody));
    }

    /**
     * 删除标签成员
     * @param $tagId
     * @param array|null $userList
     * @param array|null $partyList
     * @return mixed
     * @throws \Exception
     */
    public function delTagUser($tagId, array $userList = null, array $partyList = null)
    {
        ExceptionHelper::ThrowIfNotID($tagId, '缺少tagId');
        ExceptionHelper::ThrowIfNotTrue($userList ? is_array($userList) : true, 'userList格式不正确');
        ExceptionHelper::ThrowIfNotTrue($partyList ? is_array($partyList) : true, '$partyList格式不正确');
        ExceptionHelper::ThrowIfNotTrue(!empty($userList) || !empty($partyList), 'userList、partyList不能同时为空');
        $postBody = array(
            'tagid' => $tagId,
            'userlist' => $userList,
            'partylist' => $partyList
        );
        return $this->app->post(API::TAG_DEL_USERS, $this->app->formatBody($postBody));
    }

    /**
     * 获取标签列表
     */
    public function listTag()
    {
        return $this->app->get(API::TAG_LIST);
    }
}