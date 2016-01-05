<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/16
 * Time: 20:05
 */

namespace Xaircraft\Database;


abstract class TableQueryExecutor
{
    public static function makeSelect(
        TableSchema $schema,
        $softDeleteLess,
        array $selectFields,
        array $conditions,
        array $joins,
        array $orders,
        array $groups,
        array $havings,
        array $selectQuerySettings)
    {
        return new SelectTableQueryExecutor(
            $schema,
            $softDeleteLess,
            $selectFields,
            $conditions,
            $joins,
            $orders,
            $groups,
            $havings,
            $selectQuerySettings
        );
    }

    public static function makeUpdate($schema, $updates, $conditions)
    {
        return new UpdateTableQueryExecutor($schema, $updates, $conditions);
    }

    public static function makeDelete($schema, $conditions, $forceDelete)
    {
        return new DeleteTableQueryExecutor($schema, $conditions, $forceDelete);
    }

    public static function makeInsert($schema, $inserts, $insertGetId)
    {
        return new InsertTableQueryExecutor($schema, $inserts, $insertGetId);
    }

    public abstract function execute(QueryContext $context);

    public abstract function toQueryString(QueryContext $context);
}