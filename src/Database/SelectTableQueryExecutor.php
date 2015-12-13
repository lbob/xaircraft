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

    /**
     * @var QueryContext
     */
    private $context;

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

    public function __construct(
        TableSchema $schema,
        QueryContext $context,
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
        $this->context = $context;
        $this->joins = $joins;
        $this->orders = $orders;
        $this->groups = $groups;
        $this->havings = $havings;
        $this->selectQuerySettings = $selectQuerySettings;

        $this->parseSettings();
    }

    public function execute()
    {
        $query = $this->toQueryString();

        return $this->getQueryResult($query);
    }

    private function getQueryResult($query)
    {
        $result = DB::select($query, $this->context->getParams());

        if (!empty($this->formats)) {
            $formatResult = array();
            foreach ($result as $row) {
                $item = array();
                foreach ($row as $key => $value) {
                    $item[$key] = array_key_exists($key, $this->formats) ?
                        $this->formatConvert($row, $value, $this->formats[$key]) : $value;
                }
                $formatResult[] = $item;
            }
        } else {
            $formatResult = $result;
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

    public function toQueryString()
    {
        if ($this->schema->getSoftDelete() && !$this->softDeleteLess) {
            $this->conditions[] = ConditionInfo::make(
                ConditionInfo::CONDITION_AND,
                WhereConditionBuilder::makeNormal($this->context, $this->schema->getFieldSymbol(TableSchema::SOFT_DELETE_FIELD, false), '=', 0)
            );
        }

        $selection = SelectionQueryBuilder::toString($this->context, $this->selectFields) . ' FROM ' . $this->schema->getSymbol();
        $join = JoinQueryBuilder::toString($this->context, $this->joins);
        $condition = ConditionQueryBuilder::toString($this->conditions);
        $orders = OrderQueryBuilder::toString($this->context, $this->orders);
        $groups = GroupQueryBuilder::toString($this->context, $this->groups);
        $havings = HavingQueryBuilder::toString($this->context, $this->havings);

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

            if (isset($this->takeCount) || isset($this->skipOffset)) {
                $this->limit = true;
            }
        }
    }
}