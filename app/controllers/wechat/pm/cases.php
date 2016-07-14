<?php
use Xaircraft\Web\Mvc\Controller;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2016/7/14
 * Time: 10:41
 */
class wechat_pm_cases_controller extends Controller
{
    public function index()
    {
        return $this->status('SUCCESS', \Xaircraft\Globals::STATUS_SUCCESS, $this->req->params());
    }
}