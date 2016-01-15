<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16-1-15
 * Time: 下午1:48
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class SumFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field);
        return "SUM(" . $field->getName($context) . ")";
    }
}