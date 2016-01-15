<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 19:54
 */

namespace Xaircraft\Database\Condition;


use Xaircraft\Database\QueryContext;
use Xaircraft\Database\WhereQuery;
use Xaircraft\Exception\QueryException;

class WhereExistsConditionBuilder extends  ConditionBuilder
{
    public $clause;

    public function getQueryString(QueryContext $context)
    {
        if (isset($this->clause)) {
            $whereQuery = new WhereQuery(true);
            call_user_func($this->clause, $whereQuery);
            return "EXISTS(" . $whereQuery->getQueryString($context) . ")";
        }
        throw new QueryException("WhereExists Condition build error.");
    }

    public static function make($clause)
    {
        $condition = new WhereExistsConditionBuilder();
        $condition->clause = $clause;

        return $condition;
    }
}