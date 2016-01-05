<?php
use Xaircraft\Authentication\Contract\Authorize;
use Xaircraft\Authentication\Contract\Credential;
use Xaircraft\Web\Http\Request;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 23:29
 */
class LoginAuthorize implements Authorize
{

    /**
     * @param \Xaircraft\Authentication\Contract\Credential $credential
     * @return bool
     */
    public function authorize(Credential $credential)
    {
        /** @var Request $request */
        $request = $credential->getCredential();
        //$request->param('username')
        return false;
    }
}