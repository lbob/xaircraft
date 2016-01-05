<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 20:17
 */

namespace Xaircraft\Database;


class OrderQueryBuilder
{
    public static function toString(QueryContext $context, array $orders)
    {
        if (empty($orders)) {
            return null;
        }

        $statements = array();

        foreach ($orders as $item) {
            $field = FieldInfo::make($item->field)->getName($context);
            /**
             * @var OrderInfo $item
             */
            $statements[] = "$field $item->sort";
        }

        return implode(',', $statements);
    }
}