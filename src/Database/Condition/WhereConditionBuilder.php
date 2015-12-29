<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 19:49
 */

namespace Xaircraft\Database\Condition;


use Xaircraft\Database\FieldInfo;
use Xaircraft\Database\QueryContext;
use Xaircraft\Database\Raw;
use Xaircraft\Database\WhereQuery;
use Xaircraft\DI;

class WhereConditionBuilder extends ConditionBuilder
{
    /**
     * @var FieldInfo
     */
    public $field;

    public $operator;

    public $value;

    public $clause;

    public function getQueryString(QueryContext $context)
    {
        $statements = array();
        if (!isset($this->clause)) {
            $field = $this->field->getName($context);
            if ($this->value instanceof Raw) {
                $statements[] = "$field $this->operator " . $this->value->getValue();
            } else {
                $statements[] = "$field $this->operator ?";
                $context->param($this->value);
            }
        } else {
            $whereQuery = new WhereQuery();
            call_user_func($this->clause, $whereQuery);
            $statements[] = $whereQuery->getQueryString($context);
        }

        return !empty($statements) ? '(' . implode(' ', $statements) . ')' : null;
    }

    public static function makeNormal($field, $operator, $value, $isSubQuery = false)
    {
        $builder = new WhereConditionBuilder();
        $builder->field = FieldInfo::make($field, null, null, $isSubQuery);
        $builder->operator = $operator;
        $builder->value = $value;

        return $builder;
    }

    public static function makeClause($clause)
    {
        $builder = new WhereConditionBuilder();
        $builder->clause = $clause;

        return $builder;
    }
}