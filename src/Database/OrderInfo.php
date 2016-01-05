<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 20:13
 */

namespace Xaircraft\Database;


class OrderInfo
{
    const SORT_DESC = 'DESC';
    const SORT_ASC = 'ASC';

    public $field;

    public $sort;

    public static function make($field, $sort)
    {
        $orderInfo = new OrderInfo();
        $orderInfo->field = $field;
        $orderInfo->sort = $sort;

        return $orderInfo;
    }
}