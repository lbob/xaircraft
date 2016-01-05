<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/14
 * Time: 20:31
 */

namespace Xaircraft\Database;


interface QueryStringBuilder
{
    public function getQueryString(QueryContext $context);
}