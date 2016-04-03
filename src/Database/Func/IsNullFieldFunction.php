<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/3/31
 * Time: 19:31
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class IsNullFieldFunction extends FieldFunction
{

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field)->getName($context);

        return "$field IS NULL";
    }
}