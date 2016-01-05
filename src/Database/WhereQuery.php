<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/16
 * Time: 13:34
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Condition\WhereConditionBuilder;
use Xaircraft\Database\Condition\WhereInConditionBuilder;
use Xaircraft\Database\Func\Func;
use Xaircraft\Exception\QueryException;

class WhereQuery implements QueryStringBuilder
{
    private $subQuery = false;

    private $conditions = array();

    private $selectFields = array();

    /**
     * @var TableSchema
     */
    private $subQueryTableSchema;

    private $softDeleteLess = false;

    private $limit = false;

    private $limitCount;

    private $subQueryLimit = false;

    public function __construct($subQueryLimit = false)
    {
        $this->subQueryLimit = $subQueryLimit;
    }

    private function parseWhere($args, $argsLen, $orAnd)
    {
        if (1 === $argsLen) {
            $handler = $args[0];
            if (is_callable($handler)) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeClause($handler)));
            }
        } else {
            $field = $args[0];
            if (2 === $argsLen) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeNormal($field, '=', $args[1], true)));
            }
            if (3 === $argsLen) {
                $this->addCondition(ConditionInfo::make(
                    $orAnd, WhereConditionBuilder::makeNormal($field, $args[1], $args[2], true)
                ));
            }
        }
    }

    private function parseWhereIn($field, $params, $notIn = false)
    {
        if (isset($params) && is_array($params)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereInConditionBuilder::makeNormal($field, $params, $notIn, true)
            ));
        } else if (isset($params) && is_callable($params)) {
            $this->addCondition(ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereInConditionBuilder::makeClause($field, $params, $notIn, true)
            ));
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

    public function whereIn($field, $params)
    {
        $this->parseWhereIn($field, $params);

        return $this;
    }

    public function whereNotIn($field, $params)
    {
        $this->parseWhereIn($field, $params, true);

        return $this;
    }

    public function select()
    {
        $fields = array();
        if (func_num_args() > 0) {
            foreach (func_get_args() as $item) {
                if (is_string($item)) {
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
                        $fields[] = FieldInfo::make($value, null, null, true);
                    } else {
                        if (is_callable($value)) {
                            $fields[] = FieldInfo::make($key, $key, $value, true);
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

    public function top()
    {
        $this->limit = true;
        $this->limitCount = 1;

        return $this;
    }

    public function count()
    {
        $this->selectFields = array(FieldInfo::make(Func::count("*"), 'total_count'));

        return $this;
    }

    public function from($table)
    {
        $this->subQuery = true;
        $this->subQueryTableSchema = new TableSchema($table);

        return $this;
    }

    public function softDeleteLess()
    {
        $this->softDeleteLess = true;

        return $this;
    }

    public function getQueryString(QueryContext $context)
    {
        if (!$this->subQuery) {
            if ($this->subQueryLimit) {
                throw new QueryException("Must be sub-query in [" . ConditionQueryBuilder::toString($context, $this->conditions) . "]");
            }
            return ConditionQueryBuilder::toString($context, $this->conditions);
        } else {
            $context->schema($this->subQueryTableSchema);
            if (!$this->softDeleteLess && $this->subQueryTableSchema->getSoftDelete()) {
                $this->addCondition(ConditionInfo::make(
                    ConditionInfo::CONDITION_AND,
                    WhereConditionBuilder::makeNormal($this->subQueryTableSchema->getFieldSymbol(TableSchema::SOFT_DELETE_FIELD, false), '=', 0)
                ));
            }

            $statements = array();
            $statements[] = SelectionQueryBuilder::toString($context, $this->selectFields);
            $statements[] = 'FROM ' . $this->subQueryTableSchema->getSymbol();
            $condition = ConditionQueryBuilder::toString($context, $this->conditions);
            if (isset($condition)) {
                $statements[] = "WHERE " . $condition;
            }

            return implode(' ', $statements);
        }
    }

    private function addCondition($condition)
    {
        $this->conditions[] = $condition;
    }
}