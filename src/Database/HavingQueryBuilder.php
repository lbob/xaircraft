<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 20:43
 */

namespace Xaircraft\Database;


class HavingQueryBuilder
{
    public static function toString(QueryContext $context, array $havings)
    {
        if (empty($havings)) {
            return null;
        }

        $statements = array();

        foreach ($havings as $item) {
            if (!empty($statements)) {
                $statements[] = 'AND';
            }

            /** @var HavingInfo $item */
            $statements[] = $item->getString($context);
        }

        return implode(' ', $statements);
    }
}