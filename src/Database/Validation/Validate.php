<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 10:25
 */

namespace Xaircraft\Database\Validation;


interface Validate
{
    public function valid($value);
}