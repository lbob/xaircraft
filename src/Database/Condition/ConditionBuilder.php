<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 19:47
 */

namespace Xaircraft\Database\Condition;


use Xaircraft\Database\QueryContext;

abstract class ConditionBuilder
{
    public abstract function getQueryString(QueryContext $context);
}