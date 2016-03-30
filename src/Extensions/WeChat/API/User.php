<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/9/29
 * Time: 10:59
 */

namespace Xaircraft\Extensions\WeChat\API;

use Xaircraft\Core\Json;
use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\WeChat\API;
use Xaircraft\Extensions\WeChat\API\Contract\WeChatUser;
use Xaircraft\Extensions\WeChat\Application;

class User
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

    public function create(WeChatUser $user)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($user->name, '缺少name');
        ExceptionHelper::ThrowIfSpaceOrEmpty($user->userid, '缺少userid');
        ExceptionHelper::ThrowIfNotTrue($user->mobile || $user->email || $user->weixinid, 'mobile、email、weixinid不能同时为空');
        $user->department = !empty($user->department) && is_array($user->department) ? $user->department : array($this->rootDeptId);

        $userInfo = Json::toArray(json_encode($user));
        $this->app->post(API::USER_CREATE, $this->app->formatBody($userInfo));
    }

    public function update(WeChatUser $user)
    {
        $user->validate();
        $userInfo = Json::toArray(json_encode($user));
        foreach ($userInfo as $k => $v) {
            if ($v === null) unset($userInfo[$k]);
        }
        ExceptionHelper::ThrowIfNotTrue(is_array($userInfo), 'userInfo格式不正确');

        $this->app->post(API::USER_UPDATE, $this->app->formatBody($userInfo));
    }

    public function delete($userId)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($userId, '缺少userId');

        $this->app->get(API::USER_DELETE, array('userid' => $userId));
    }

    public function batchDelete(array $userIds)
    {
        ExceptionHelper::ThrowIfNotTrue(is_array($userIds), '缺少userIds');
        $userIds = array("useridlist" => $userIds);

        $this->app->post(API::USER_BATCH_DELETE, $this->app->formatBody($userIds));
    }

    public function getInfo($userId)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($userId, '缺少userId');

        return $this->app->get(API::USER_GET, array('userid' => $userId));
    }

    /**
     * 获取部门成员
     * @param $departmentId
     * @param $fetchChild 1/0：是否递归获取子部门下面的成员
     * @param $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加，未填写则默认为4
     * @return mixed|string
     * @throws \Exception
     */
    public function simpleList($departmentId = 0, $fetchChild = 1, $status = 0)
    {
        $departmentId = $departmentId ? $departmentId : $this->rootDeptId;

        return $this->app->get(API::USER_SIMPLE_LIST, array(
            'department_id' => $departmentId,
            'fetch_child' => $fetchChild,
            'status' => $status
        ));
    }

    /**
     * 获取部门成员(详情)
     * @param $departmentId
     * @param $fetchChild 1/0：是否递归获取子部门下面的成员
     * @param $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加,未填写则默认为4
     * @return mixed|string
     */
    public function listDetail($departmentId = 0, $fetchChild = 1, $status = 0)
    {
        $departmentId = $departmentId ? $departmentId : $this->rootDeptId;

        return $this->app->get(API::USER_LIST, array(
            'department_id' => $departmentId,
            'fetch_child' => $fetchChild,
            'status' => $status
        ));
    }

    /**
     * 邀请成员关注
     * @param $userId
     * @param string $inviteTips
     * @return mixed|string
     * @throws \Exception
     */
    public function inviteSend($userId)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($userId, '缺少userId');

        $body = array('userid' => $userId);
        return $this->app->post(API::INVITE_SEND, $this->app->formatBody($body));
    }

    public function authSuccess($userId)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($userId, '缺少userId');

        $this->app->get(API::USER_AUTHSUCC, array('userid' => $userId));
    }
}