<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 15:50
 */

namespace Xaircraft\Database;


class JoinConditionInfo
{
    public $onField;

    public $onOperator;

    public $onValue;

    public $orAnd;

    public $whereCondition = false;

    public function __construct($orAnd, $onField, $onOperator, $onValue, $whereCondition)
    {
        $this->onField = $onField;
        $this->onOperator = $onOperator;
        $this->onValue = $onValue;
        $this->orAnd = $orAnd;
        $this->whereCondition = $whereCondition;
    }

    public static function make($orAnd, $onField, $onOperator, $onValue, $whereCondition)
    {
        return new JoinConditionInfo($orAnd, $onField, $onOperator, $onValue, $whereCondition);
    }
}