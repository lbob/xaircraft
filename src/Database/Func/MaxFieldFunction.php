<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/23 0023
 * Time: 14:20
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class MaxFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field);
        return "MAX(" . $field->getName($context) . ")";
    }
}