<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 15:13
 */

namespace Xaircraft\Web\Http;


class HttpContext
{
    /**
     * @var \Xaircraft\Web\Http\Request
     */
    private $request;

    /**
     * @var \Xaircraft\Web\Http\Response
     */
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }
}