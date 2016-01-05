<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 15:53
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Condition\ConditionBuilder;

class ConditionInfo
{
    const CONDITION_OR = 'OR';
    const CONDITION_AND = 'AND';

    public $orAnd;

    /**
     * @var ConditionBuilder
     */
    public $conditionBuilder;

    public static function make($orAnd, ConditionBuilder $builder)
    {
        $condition = new ConditionInfo();
        $condition->orAnd = $orAnd;
        $condition->conditionBuilder = $builder;

        return $condition;
    }
}