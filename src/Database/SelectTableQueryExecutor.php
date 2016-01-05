<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/16
 * Time: 20:01
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Condition\WhereConditionBuilder;
use Xaircraft\Database\Data\FieldFormatInfo;
use Xaircraft\Database\Data\FieldType;
use Xaircraft\Database\Data\NumberFieldType;
use Xaircraft\Database\Data\TextFieldType;
use Xaircraft\Database\Data\TimestampFieldType;
use Xaircraft\DB;

class SelectTableQueryExecutor extends TableQueryExecutor
{
    private $schema;

    private $selectFields;

    private $conditions;

    private $softDeleteLess;

    private $joins;

    private $orders;

    private $groups;

    private $havings;

    private $selectQuerySettings;

    private $takeCount;

    private $skipOffset;

    private $pluck;

    private $limit;

    private $formats;

    private $singleField = false;

    private $detail = false;

    public function __construct(
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
        $this->schema = $schema;
        $this->selectFields = $selectFields;
        $this->conditions = $conditions;
        $this->softDeleteLess = $softDeleteLess;
        $this->joins = $joins;
        $this->orders = $orders;
        $this->groups = $groups;
        $this->havings = $havings;
        $this->selectQuerySettings = $selectQuerySettings;

        $this->parseSettings();
    }

    public function execute(QueryContext $context)
    {
        $query = $this->toQueryString($context);

        return $this->getQueryResult($context, $query);
    }

    private function getQueryResult(QueryContext $context, $query)
    {
        $result = DB::select($query, $context->getParams());

        if (!empty($this->formats)) {
            $formatResult = array();
            foreach ($result as $row) {
                $item = array();
                foreach ($row as $key => $value) {
                    $item[$key] = array_key_exists($key, $this->formats) ?
                        $this->formatConvert($row, $this->getFieldValue($key, $value), $this->formats[$key]) : $this->getFieldValue($key, $value);
                }
                $formatResult[] = $item;
            }
        } else {
            $formatResult = array();
            foreach ($result as $row) {
                $item = array();
                foreach ($row as $key => $value) {
                    $item[$key] = $this->getFieldValue($key, $value);
                }
                $formatResult[] = $item;
            }
        }

        if (!empty($formatResult)) {
            if ($this->pluck) {
                /** @var FieldInfo $field */
                $field = $this->selectFields[0];
                $alias = $field->getAlias();
                if (isset($alias)) {
                    return $formatResult[0][$alias];
                }
                return $formatResult[0][$field->getField()];
            }

            if ($this->singleField) {
                $result = array();
                foreach ($formatResult as $row) {
                    if (!empty($row)) {
                        foreach ($row as $key => $value) {
                            $result[] = $value;
                            break;
                        }
                    }
                }
                return $result;
            }

            if ($this->detail) {
                return isset($formatResult[0]) ? $formatResult[0] : null;
            }
        } else {
            if ($this->pluck || $this->detail) {
                return null;
            }
        }

        return $formatResult;
    }

    private function getFieldValue($name, $value)
    {
        if (isset($name)) {
            $field = $this->schema->field($name);
            if (isset($field) && isset($field->fieldType)) {
                return $field->fieldType->convert($value);
            }
        }

        return $value;
    }

    private function formatConvert($row, $value, $format)
    {
        if (is_callable($format)) {
            $result = call_user_func($format, $value, $row);
            return $result;
        } else {
            switch ($format) {
                case FieldType::TEXT:
                    $convert = new TextFieldType();
                    return $convert->convert($value);
                case FieldType::NUMBER:
                    $convert = new NumberFieldType();
                    return $convert->convert($value);
                case FieldType::DATE:
                    $convert = new TimestampFieldType();
                    return $convert->convert($value);
                default:
                    return $value;
            }
        }
    }

    public function toQueryString(QueryContext $context)
    {
        if ($this->schema->getSoftDelete() && !$this->softDeleteLess) {
            $this->conditions[] = ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereConditionBuilder::makeNormal($this->schema->getFieldSymbol(TableSchema::SOFT_DELETE_FIELD, false), '=', 0)
            );
        }

        $selection = SelectionQueryBuilder::toString($context, $this->selectFields) . ' FROM ' . $this->schema->getSymbol();
        $join = JoinQueryBuilder::toString($context, $this->joins);
        $condition = ConditionQueryBuilder::toString($context, $this->conditions);
        $orders = OrderQueryBuilder::toString($context, $this->orders);
        $groups = GroupQueryBuilder::toString($context, $this->groups);
        $havings = HavingQueryBuilder::toString($context, $this->havings);

        $statements = array($selection);

        if (isset($join)) {
            $statements[] = $join;
        }

        if (isset($condition)) {
            $statements[] = "WHERE $condition";
        }

        if (isset($orders)) {
            $statements[] = "ORDER BY $orders";
        }

        if (isset($groups)) {
            $statements[] = "GROUP BY $groups";
        }

        if (isset($havings)) {
            $statements[] = "HAVING ($havings)";
        }

        if (isset($this->limit)) {
            if ($this->skipOffset > 0 && $this->takeCount > 0) {
                $statements[] = "LIMIT $this->skipOffset,$this->takeCount";
            } else if ($this->takeCount > 0) {
                $statements[] = "LIMIT $this->takeCount";
            }
        }

        return implode(' ', $statements);
    }

    private function parseSettings()
    {
        if (isset($this->selectQuerySettings)) {
            $settings = $this->selectQuerySettings;
            $this->takeCount = array_key_exists('take_count', $settings) ? $settings['take_count'] : null;
            $this->skipOffset = array_key_exists('skip_offset', $settings) ? $settings['skip_offset'] : null;
            $this->pluck = array_key_exists('pluck', $settings) ? $settings['pluck'] : null;
            $this->formats = array_key_exists('formats', $settings) ? $settings['formats'] : null;
            $this->singleField = array_key_exists('single_field', $settings) ? $settings['single_field'] : false;
            $this->detail = array_key_exists('detail', $settings) ? $settings['detail'] : false;

            if (isset($this->takeCount) || isset($this->skipOffset)) {
                $this->limit = true;
            }
        }
    }
}