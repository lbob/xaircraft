<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/19
 * Time: 20:00
 */

namespace Xaircraft\Database;


use Xaircraft\DB;
use Xaircraft\Exception\DataTableException;

class DeleteTableQueryExecutor extends TableQueryExecutor
{
    /**
     * @var TableSchema
     */
    private $schema;
    private $conditions;
    private $forceDelete = false;

    public function __construct(TableSchema $schema, $conditions, $forceDelete)
    {
        $this->schema = $schema;
        $this->conditions = $conditions;
        $this->forceDelete = $forceDelete;
    }

    public function execute(QueryContext $context)
    {
        if (!$this->forceDelete && $this->schema->getSoftDelete()) {
            $executor = TableQueryExecutor::makeUpdate($this->schema, array(
                TableSchema::SOFT_DELETE_FIELD => time()
            ), $this->conditions);

            return $executor->execute($context);
        }

        $query = $this->toQueryString($context);

        return DB::delete($query, $context->getParams());
    }

    public function toQueryString(QueryContext $context)
    {
        if (empty($this->conditions)) {
            throw new DataTableException($this->schema->getSymbol(), "Can't execute DELETE query without conditions.");
        }

        $statements = array(
            "DELETE FROM",
            $this->schema->getSymbol(),
            "WHERE",
            ConditionQueryBuilder::toString($context, $this->conditions)
        );

        return implode(' ', $statements);
    }
}