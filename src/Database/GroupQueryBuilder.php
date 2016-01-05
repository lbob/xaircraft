<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 20:25
 */

namespace Xaircraft\Database;


class GroupQueryBuilder
{
    public static function toString(QueryContext $context, $groups)
    {
        if (empty($groups)) {
            return null;
        }

        $fields = array();
        /** @var GroupInfo $item */
        foreach ($groups as $item) {
            if (!empty($item->groups)) {
                foreach ($item->groups as $group) {
                    $fields[] = FieldInfo::make($group)->getName($context);
                }
            }
        }


        return implode(',', $fields);
    }
}