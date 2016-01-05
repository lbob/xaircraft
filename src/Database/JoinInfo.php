<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/17
 * Time: 15:55
 */

namespace Xaircraft\Database;



class JoinInfo
{
    public $table;

    public $condition;

    public $clause;

    public $leftJoin = false;

    /**
     * @var TableSchema
     */
    public $schema;

    public static function makeNormal($table, JoinConditionInfo $condition, $leftJoin = false)
    {
        $joinInfo = new JoinInfo();
        $joinInfo->table = $table;
        $joinInfo->condition = $condition;
        $joinInfo->leftJoin = $leftJoin;

        $joinInfo->parseTable();

        return $joinInfo;
    }

    public static function makeClause($table, $clause, $leftJoin = false)
    {
        $joinInfo = new JoinInfo();
        $joinInfo->table = $table;
        $joinInfo->clause = $clause;
        $joinInfo->leftJoin = $leftJoin;

        $joinInfo->parseTable();

        return $joinInfo;
    }

    private function parseTable()
    {
        $this->schema = new TableSchema($this->table);
    }
}