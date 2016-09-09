<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/23 0023
 * Time: 14:20
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class MinFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field);
        return "MIN(" . $field->getName($context) . ")";
    }
}