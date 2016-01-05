<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/6
 * Time: 21:47
 */

namespace Xaircraft\Authentication\Contract;


interface Authorize
{
    /**
     * @param Credential $credential
     * @return bool
     */
    public function authorize(Credential $credential);
}