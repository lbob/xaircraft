<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/16
 * Time: 15:41
 */

namespace Xaircraft\Database;


use Xaircraft\Exception\QueryException;

class SelectionQueryBuilder
{
    public static function toString(QueryContext $context, array $fields)
    {
        $statements = array();

        if (!isset($fields) || empty($fields)) {
            $statements[] = "*";
        } else {
            foreach ($fields as $field) {
                /**
                 * @var FieldInfo $field
                 */
                if (isset($field->queryHandler) && is_callable($field->queryHandler)) {
                    $whereQuery = new WhereQuery(true);
                    call_user_func($field->queryHandler, $whereQuery);
                    $item = '(' . $whereQuery->getQueryString($context) . ') AS ' . $field->getAlias();
                    if ($item === '') {
                        throw new QueryException("Sub-query error in Selection [" . implode(' ', $statements) . "]");
                    }
                } else {
                    $value = $field->getValue();
                    if (isset($value)) {
                        $item = $field->getValueColumnSymbol();
                    } else {
                        $item = $field->getName($context);
                    }
                }

                $statements[] = $item;
            }
        }

        return "SELECT " . implode(',', $statements);
    }
}