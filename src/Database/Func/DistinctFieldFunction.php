<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/29
 * Time: 14:18
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\FieldInfo;
use Xaircraft\Database\QueryContext;

class DistinctFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        if ("*" !== $this->field) {
            $field = $context->makeFieldInfo($this->field);
            return "DISTINCT(" . $field->getName($context) . ")";
        }
        return "DISTINCT(*)";
    }
}