<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 21:54
 */

namespace Xaircraft\Web\Mvc;


use Xaircraft\Authentication\Contract\Credential;
use Xaircraft\DI;
use Xaircraft\Web\Http\Request;

class HttpAuthCredential implements Credential
{
    /**
     * @return Request
     */
    public function getCredential()
    {
        return DI::get(Request::class);
    }
}