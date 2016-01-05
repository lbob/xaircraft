<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/16
 * Time: 16:09
 */

namespace Xaircraft\Database;


use Xaircraft\Exception\QueryException;

class ConditionQueryBuilder
{
    public static function toString(QueryContext $context, array $conditions)
    {
        $statements = array();

        foreach ($conditions as $condition) {
            /**
             * @var ConditionInfo $condition
             */
            if (count($statements) > 0) {
                $statements[] = $condition->orAnd;
            }
            $item = $condition->conditionBuilder->getQueryString($context);
            if (!isset($item)) {
                throw new QueryException("Condition query string build error.");
            }
            $statements[] = $item;
        }

        return !empty($statements) ? implode(' ', $statements) : null;
    }
}