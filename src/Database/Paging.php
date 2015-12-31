<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/31
 * Time: 15:15
 */

namespace Xaircraft\Database;


class Paging
{
    public static function execute(TableQuery $query, $pageIndex, $pageSize)
    {
        $dataQuery = clone $query;
        $recordCount = $query->count()->execute();
        $data = $dataQuery->page($pageIndex, $pageSize)->execute();

        $result = array();

        $result['recordCount'] = $recordCount;
        $result['pageCount'] = $pageSize > 0 ? intval(($recordCount / $pageSize) + ($recordCount % $pageSize === 0 ? 0 : 1)) : 0;
        $result['data'] = $data;

        return $result;
    }
}