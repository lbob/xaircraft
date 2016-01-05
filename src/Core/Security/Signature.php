<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/31
 * Time: 11:59
 */

namespace Xaircraft\Security;


interface Signature
{
    public function signature($content);
}