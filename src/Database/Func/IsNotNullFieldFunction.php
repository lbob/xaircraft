<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/3/31
 * Time: 19:35
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class IsNotNullFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field)->getName($context);

        return "$field IS NOT NULL";
    }
}