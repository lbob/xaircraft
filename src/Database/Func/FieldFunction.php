<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/11
 * Time: 16:27
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

abstract class FieldFunction
{
    public $field;

    public function __construct($field)
    {
        $this->field = trim($field);
    }

    public abstract function getString(QueryContext $context);
}