<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/11
 * Time: 16:36
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\FieldInfo;
use Xaircraft\Database\QueryContext;

class CountFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        if ("*" !== $this->field) {
            $field = $context->makeFieldInfo($this->field);
            return "COUNT(" . $field->getName($context) . ")";
        }
        return "COUNT(*)";
    }
}