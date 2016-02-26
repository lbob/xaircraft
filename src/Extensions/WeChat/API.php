<?php

namespace Xaircraft\Extensions\WeChat;


/**
 * Class API
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 16:57
 */
class API {

    /**
     * 获取access_token
     */
    const GET_ACCESS_TOKEN = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={corpid}&corpsecret={corpsecret}";
    /**
     * 获取JsApiTicket
     */
    const GET_JS_API_TICKET = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token={ACCESS_TOKEN}";

    /**
     * 发送消息
     */
    const SEND_MESSAGE = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={ACCESS_TOKEN}";

    /**
     * 创建标签
     */
    const TAG_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/tag/create?access_token={ACCESS_TOKEN}";
    /**
     * 更新标签名字
     */
    const TAG_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/tag/update?access_token={ACCESS_TOKEN}";
    /**
     * 删除标签
     */
    const TAG_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/tag/delete?access_token={ACCESS_TOKEN}&tagid={tagid}";
    /**
     *获取标签成员
     */
    const TAG_GET = "https://qyapi.weixin.qq.com/cgi-bin/tag/get?access_token={ACCESS_TOKEN}&tagid={tagid}";
    /**
     *增加标签成员
     */
    const TAG_ADD_USERS = "https://qyapi.weixin.qq.com/cgi-bin/tag/addtagusers?access_token={ACCESS_TOKEN}";
    /**
     *删除标签成员
     */
    const TAG_DEL_USERS = "https://qyapi.weixin.qq.com/cgi-bin/tag/deltagusers?access_token={ACCESS_TOKEN}";
    /**
     * 获取标签列表
     */
    const TAG_LIST = "https://qyapi.weixin.qq.com/cgi-bin/tag/list?access_token={ACCESS_TOKEN}";

    /**
     * 创建部门
     */
    const DEPARTMENT_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token={ACCESS_TOKEN}";
    /**
     * 更新部门
     */
    const DEPARTMENT_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token={ACCESS_TOKEN}";
    /**
     * 删除部门
     */
    const DEPARTMENT_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token={ACCESS_TOKEN}&id={id}";
    /**
     * 获取部门列表
     */
    const DEPARTMENT_LIST = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={ACCESS_TOKEN}&id={id}";

    /**
     * 创建成员
     */
    const USER_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token={ACCESS_TOKEN}";
    /**
     * 更新成员
     */
    const USER_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token={ACCESS_TOKEN}";
    /**
     * 删除成员
     */
    const USER_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token={ACCESS_TOKEN}&userid={userid}";
    /**
     * 批量删除成员
     */
    const USER_BATCH_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/user/batchDelete?access_token={ACCESS_TOKEN}";
    /**
     * 获取成员
     */
    const USER_GET = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token={ACCESS_TOKEN}&userid={userid}";
    /**
     * 获取部门成员列表(简单)
     */
    const USER_SIMPLE_LIST = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token={ACCESS_TOKEN}&department_id={department_id}&fetch_child={fetch_child}&status={status}";
    /**
     * 获取部门成员列表(详细)
     */
    const USER_LIST = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={ACCESS_TOKEN}&department_id={department_id}&fetch_child={fetch_child}&status={status}";
    /**
     * 邀请成员关注
     */
    const INVITE_SEND = "https://qyapi.weixin.qq.com/cgi-bin/invite/send?access_token={ACCESS_TOKEN}";
    /**
     * 让成员关注成功
     */
    const USER_AUTHSUCC = "https://qyapi.weixin.qq.com/cgi-bin/user/authsucc?access_token={ACCESS_TOKEN}&userid={userid}";

    /**
     * 验证URL
     */
    const AUTHORIZE_URL = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={corpid}&redirect_uri={REDIRECT_URI}&response_type=code&scope=snsapi_base&state={STATE}#wechat_redirect";

    /**
     * 根据CODE获取成员信息
     */
    const AUTHORIZE_GETUSERINFO = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={ACCESS_TOKEN}&code={CODE}&agentid={AGENTID}";

    /**
     * 获取素材列表
     */
    const MEDIA_LIST = "https://qyapi.weixin.qq.com/cgi-bin/material/batchget?access_token={ACCESS_TOKEN}";
    /**
     * 获取临时素材文件
     */
    const MEDIA_TEMPORARY = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token={ACCESS_TOKEN}&media_id={media_id}";
    /**
     * 获取永久素材文件
     */
    const MEDIA_FOREVER = "https://qyapi.weixin.qq.com/cgi-bin/material/get?access_token={ACCESS_TOKEN}&media_id={media_id}&agentid={AGENTID}";
}

