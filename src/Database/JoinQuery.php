<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 10:18
 */

namespace Xaircraft\Database;


class JoinQuery
{
    private $conditions = array();

    private $softDeleteLess = false;

    public function on($field)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseCondition(ConditionInfo::CONDITION_AND, $field, $args, $argsLen);

        return $this;
    }

    public function orOn($field)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseCondition(ConditionInfo::CONDITION_OR, $field, $args, $argsLen);

        return $this;
    }

    public function where($field)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseCondition(ConditionInfo::CONDITION_AND, $field, $args, $argsLen, true);

        return $this;
    }

    public function orWhere($field)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseCondition(ConditionInfo::CONDITION_OR, $field, $args, $argsLen, true);

        return $this;
    }

    private function parseCondition($orAnd, $field, $args, $argsLen, $whereCondition = false)
    {
        if (2 === $argsLen) {
            $this->conditions[] = JoinConditionInfo::make($orAnd, $field, '=', $args[1], $whereCondition);
        }
        if (3 === $argsLen) {
            $this->conditions[] = JoinConditionInfo::make($orAnd, $field, $args[1], $args[2], $whereCondition);
        }
    }

    public function softDeleteLess()
    {
        $this->softDeleteLess = true;
    }

    public function getSoftDeleteLess()
    {
        return $this->softDeleteLess;
    }

    public function getConditions()
    {
        return $this->conditions;
    }
}