<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/14
 * Time: 20:55
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Condition\ConditionBuilder;
use Xaircraft\Database\Condition\WhereBetweenConditionBuilder;
use Xaircraft\Database\Condition\WhereConditionBuilder;
use Xaircraft\Database\Condition\WhereExistsConditionBuilder;
use Xaircraft\Database\Condition\WhereInConditionBuilder;
use Xaircraft\Database\Data\FieldFormatInfo;
use Xaircraft\Database\Func\FieldFunction;
use Xaircraft\Database\Func\Func;
use Xaircraft\DB;
use Xaircraft\DI;
use Xaircraft\Exception\DataTableException;
use Xaircraft\Exception\QueryException;

class TableQuery implements QueryStringBuilder
{
    /**
     * @var TableSchema
     */
    private $schema;

    /**
     * @var QueryContext
     */
    //private $context;

    const QUERY_SELECT = 'QUERY_SELECT';
    const QUERY_INSERT = 'QUERY_INSERT';
    const QUERY_UPDATE = 'QUERY_UPDATE';
    const QUERY_DELETE = 'QUERY_DELETE';
    const QUERY_TRUNCATE = 'QUERY_TRUNCATE';

    private $queryType;

    private $conditions = array();

    private $selectFields = array();

    private $softDeleteLess = false;

    private $joins = array();

    private $orders = array();

    private $groups = array();

    private $havings = array();

    private $selectQuerySettings = array();

    private $updates;

    private $inserts;

    private $forceDelete = false;

    private $insertGetId = false;

    public function __construct($table)
    {
        $this->schema = new TableSchema($table);
    }

    public function getTableName()
    {
        return $this->schema->getName();
    }

    public function getTableSchema()
    {
        return $this->schema;
    }

    public function getQueryType()
    {
        return $this->queryType;
    }

    public function execute(QueryContext $context = null)
    {
        $context = isset($context) ? $context : DI::get(QueryContext::class);

        $tableQueryExecutor = $this->parseTableQuery($context);

        $result = null;
        if (isset($tableQueryExecutor)) {
            $result = $tableQueryExecutor->execute($context);
        }

        return $result;
    }

    private function parseTableQuery(QueryContext $context)
    {
        $context->schema($this->schema);

        switch ($this->queryType) {
            case self::QUERY_SELECT:
                return TableQueryExecutor::makeSelect(
                    $this->schema,
                    $this->softDeleteLess,
                    $this->selectFields,
                    $this->conditions,
                    $this->joins,
                    $this->orders,
                    $this->groups,
                    $this->havings,
                    $this->selectQuerySettings
                );
            case self::QUERY_UPDATE:
                return TableQueryExecutor::makeUpdate(
                    $this->schema,
                    $this->updates,
                    $this->conditions
                );
            case self::QUERY_DELETE:
                return TableQueryExecutor::makeDelete(
                    $this->schema,
                    $this->conditions,
                    $this->forceDelete
                );
            case self::QUERY_INSERT:
                return TableQueryExecutor::makeInsert(
                    $this->schema,
                    $this->inserts,
                    $this->insertGetId
                );
        }

        return null;
    }

    public function update(array $updates)
    {
        $this->queryType = self::QUERY_UPDATE;

        $this->updates = $updates;

        return $this;
    }

    public function insert(array $inserts)
    {
        $this->queryType = self::QUERY_INSERT;

        $this->inserts = $inserts;

        return $this;
    }

    public function insertGetId(array $inserts)
    {
        $this->queryType = self::QUERY_INSERT;

        $this->inserts = $inserts;
        $this->insertGetId = true;

        return $this;
    }

    public function delete()
    {
        $this->queryType = self::QUERY_DELETE;

        return $this;
    }

    public function forceDelete()
    {
        $this->queryType = self::QUERY_DELETE;
        $this->forceDelete = true;

        return $this;
    }

    public function count()
    {
        $this->queryType = self::QUERY_SELECT;
        $this->selectFields = array(FieldInfo::make(Func::count("*"), 'total_count'));
        $this->selectQuerySettings['pluck'] = true;

        return $this;
    }

    public function select()
    {
        $this->queryType = self::QUERY_SELECT;

        $fields = array();
        if (func_num_args() > 0) {
            foreach (func_get_args() as $item) {
                if (is_string($item) || $item instanceof FieldFunction) {
                    $fields[] = FieldInfo::make($item);
                }
            }
        }
        if (1 === func_num_args()) {
            $params = func_get_arg(0);
            if (isset($params) && is_array($params)) {
                $fields = array();
                foreach ($params as $key => $value) {
                    if (!is_string($key)) {
                        $fields[] = FieldInfo::make($value);
                    } else {
                        if (is_callable($value)) {
                            $fields[] = FieldInfo::make($key, $key, $value);
                        } else if ($value instanceof FieldFunction) {
                            $fields[] = FieldInfo::make($value, $key);
                        } else {
                            $fields[] = FieldInfo::makeValueColumn($key, $value);
                        }
                    }
                }
            }
        }

        $this->selectFields = $fields;

        return $this;
    }

    public function single()
    {
        $this->queryType = self::QUERY_SELECT;

        $this->selectQuerySettings['single_field'] = true;

        return $this;
    }

    public function detail()
    {
        $this->queryType = self::QUERY_SELECT;

        $this->selectQuerySettings['detail'] = true;

        return $this;
    }

    public function format(array $formats)
    {
        $this->queryType = self::QUERY_SELECT;

        $this->selectQuerySettings['formats'] = $formats;

        return $this;
    }

    public function take($count)
    {
        $this->queryType = self::QUERY_SELECT;

        if ($count <= 0) {
            throw new QueryException("Selection query error. take $count.");
        }

        $this->selectQuerySettings['take_count'] = $count;

        return $this;
    }

    public function skip($offset)
    {
        $this->queryType = self::QUERY_SELECT;

        if ($offset < 0) {
            throw new QueryException("Selection query error. skip $offset.");
        }

        $this->selectQuerySettings['skip_offset'] = $offset;

        return $this;
    }

    public function page($pageIndex = 1, $pageSize)
    {
        $offset = ($pageIndex - 1) * $pageSize;
        $offset = $offset > 0 ? $offset : 0;

        return $this->take($pageSize)->skip($offset);
    }

    public function pluck($field = null)
    {
        $this->queryType = self::QUERY_SELECT;

        $this->selectQuerySettings['pluck'] = true;

        if (isset($field)) {
            $this->select($field);
        }

        $this->take(1);

        return $this;
    }

    public function orderBy($field, $sort = OrderInfo::SORT_ASC)
    {
        $this->orders[] = OrderInfo::make($field, $sort);

        return $this;
    }

    public function groupBy()
    {
        $fields = func_get_args();
        $this->groups[] = GroupInfo::make($fields);

        return $this;
    }

    public function having($field)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        if (2 === $argsLen) {
            $this->havings[] = HavingInfo::make($field, '=', $args[1]);
        }

        if (3 === $argsLen) {
            $this->havings[] = HavingInfo::make($field, $args[1], $args[2]);
        }

        return $this;
    }

    public function join($table)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseJoin($table, $args, $argsLen, false);

        return $this;
    }

    public function leftJoin($table)
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseJoin($table, $args, $argsLen, true);

        return $this;
    }

    private function parseJoin($table, $args, $argsLen, $leftJoin = false)
    {
        if (2 === $argsLen) {
            $clause = $args[1];
            if (isset($clause) && is_callable($clause)) {
                $this->joins[] = JoinInfo::makeClause($table, $clause, $leftJoin);
            } else {
                throw new QueryException("Join query error - it should be a sub-query clause here. [$table]");
            }
        }
        if (3 === $argsLen) {
            $this->joins[] = JoinInfo::makeNormal(
                $table,
                JoinConditionInfo::make(ConditionInfo::CONDITION_AND, $args[1], '=', $args[2], false),
                $leftJoin
            );
        }
        if (4 === $argsLen) {
            $this->joins[] = JoinInfo::makeNormal(
                $table,
                JoinConditionInfo::make(ConditionInfo::CONDITION_AND, $args[1], $args[2], $args[3], false),
                $leftJoin
            );
        }
    }

    private function parseWhere($args, $argsLen, $orAnd)
    {
        if (1 === $argsLen) {
            $handler = $args[0];
            if (is_callable($handler)) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeClause($handler)));
            }
            if ($handler instanceof FieldFunction) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeNormal($handler, null, null)
                ));
            }
        } else {
            $field = $args[0];
            if (2 === $argsLen) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeNormal($field, '=', $args[1])));
            }
            if (3 === $argsLen) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeNormal($field, $args[1], $args[2])
                ));
            }
        }
    }

    public function where()
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseWhere($args, $argsLen, ConditionInfo::CONDITION_AND);

        return $this;
    }

    public function orWhere()
    {
        $args = func_get_args();
        $argsLen = func_num_args();

        $this->parseWhere($args, $argsLen, ConditionInfo::CONDITION_OR);

        return $this;
    }

    public function whereBetween($field, array $ranges)
    {
        if (2 === count($ranges)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereBetweenConditionBuilder::makeBetween($field, $ranges)));
        }

        return $this;
    }

    public function whereNotBetween($field, array $ranges)
    {
        if (2 === count($ranges)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereBetweenConditionBuilder::makeNotBetween($field, $ranges)));
        }

        return $this;
    }

    private function parseWhereIn($field, $params, $notIn = false, $orAnd = ConditionInfo::CONDITION_AND)
    {
        if (isset($params) && is_array($params)) {
            $this->addCondition(ConditionInfo::make(
                $orAnd,
                WhereInConditionBuilder::makeNormal($field, $params, $notIn)
            ));
        } else if (isset($params) && is_callable($params)) {
            $this->addCondition(ConditionInfo::make(
                $orAnd,
                WhereInConditionBuilder::makeClause($field, $params, $notIn)
            ));
        }
    }

    public function whereIn($field, $params)
    {
        $this->parseWhereIn($field, $params);

        return $this;
    }

    public function orWhereIn($field, $params)
    {
        $this->parseWhereIn($field, $params, false, ConditionInfo::CONDITION_OR);

        return $this;
    }

    public function whereNotIn($field, $params)
    {
        $this->parseWhereIn($field, $params, true);

        return $this;
    }

    public function whereExists($clause)
    {
        if (isset($clause) && is_callable($clause)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereExistsConditionBuilder::make($clause)
            ));
        }

        return $this;
    }

    public function orWhereExists($clause)
    {
        if (isset($clause) && is_callable($clause)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_OR,
                WhereExistsConditionBuilder::make($clause)
            ));
        }

        return $this;
    }

    public function softDeleteLess()
    {
        $this->softDeleteLess = true;

        return $this;
    }

    public function getQueryString(QueryContext $context = null)
    {
        $context = isset($context) ? $context : DI::get(QueryContext::class);

        $tableQueryExecutor = $this->parseTableQuery($context);

        $this->contextLock = true;

        if (isset($tableQueryExecutor)) {
            return $tableQueryExecutor->toQueryString($context);
        }
        return null;
    }

    private function addCondition($condition)
    {
        $this->conditions[] = $condition;
    }
}
