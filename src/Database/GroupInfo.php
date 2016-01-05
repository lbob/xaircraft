<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 20:24
 */

namespace Xaircraft\Database;


use Xaircraft\Exception\QueryException;

class GroupInfo
{
    public $groups;

    public static function make(array $groups)
    {
        if (!isset($groups) || empty($groups)) {
            throw new QueryException("Can't execute method [groupBy()] without parameters.");
        }

        $groupInfo = new GroupInfo();
        $groupInfo->groups = $groups;

        return $groupInfo;
    }
}