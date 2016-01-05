<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/19
 * Time: 16:58
 */

namespace Xaircraft\Database;


use Xaircraft\DB;
use Xaircraft\Exception\DataTableException;

class UpdateTableQueryExecutor extends TableQueryExecutor
{
    /**
     * @var TableSchema
     */
    private $schema;
    private $updates;
    private $conditions;

    public function __construct(TableSchema $schema, array $updates, array $conditions)
    {
        $this->schema = $schema;
        $this->updates = $updates;
        $this->conditions = $conditions;

        if (empty($conditions)) {
            throw new DataTableException($schema->getSymbol(), "Can't execute UPDATE query without conditions.");
        }
    }

    public function execute(QueryContext $context)
    {
        $query = $this->toQueryString($context);

        return DB::update($query, $context->getParams());
    }

    public function toQueryString(QueryContext $context)
    {
        $updatetion = UpdatetionQueryBuilder::toString($this->schema, $context, $this->updates);
        $condition = ConditionQueryBuilder::toString($context, $this->conditions);

        $statements = array();

        $statements[] = "UPDATE " . $this->schema->getSymbol();
        $statements[] = $updatetion;
        $statements[] = "WHERE " . $condition;

        return implode(' ', $statements);
    }
}