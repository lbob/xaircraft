<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2016/7/14
 * Time: 12:04
 */
class TestHttpModule extends \Xaircraft\Web\HttpModule
{

    public function start()
    {
        /** @var \Xaircraft\Web\Http\Request $request */
        $request = \Xaircraft\DI::get(\Xaircraft\Web\Http\Request::class);

        //var_dump($request->param('workspace'));
    }

    public function end()
    {

    }
}