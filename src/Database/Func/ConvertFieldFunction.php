<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 2016/3/16
 * Time: 10:33
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\QueryContext;

class ConvertFieldFunction extends FieldFunction
{
    const USING_GBK = 'USING GBK';

    private $type;

    public function __construct($field, $type)
    {
        parent::__construct($field);

        $this->type = $type;
    }

    public function getString(QueryContext $context)
    {
        $field = $context->makeFieldInfo($this->field);
        return "CONVERT(" . $field->getName($context) . " " . $this->type . ")";
    }
}