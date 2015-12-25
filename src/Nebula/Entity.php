<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 15:10
 */

namespace Xaircraft\Nebula;


use Xaircraft\Database\TableQuery;
use Xaircraft\DB;
use Xaircraft\Exception\EntityException;

class Entity
{
    private $fields = array();

    private $exists = false;

    private $shadows;

    /**
     * @var TableQuery
     */
    private $query;

    private $updates = array();
    /**
     * @var \Xaircraft\Database\TableSchema
     */
    private $schema;

    private $autoIncrementField;

    public function __construct($arg)
    {
        if (!isset($arg)) {
            throw new EntityException("Undefined table name or query.");
        }

        if (is_string($arg)) {
            $this->query = DB::table($arg);
        }

        if ($arg instanceof TableQuery) {
            if (TableQuery::QUERY_SELECT !== $arg->getQueryType()) {
                throw new EntityException("Must be selection table query.");
            }

            $this->query = $arg;
        }

        $this->schema = $this->query->getTableSchema();
        $this->autoIncrementField = $this->schema->getAutoIncrementField();

        $this->load();
    }

    public function save(array $fields = null)
    {
        $this->parseUpdateFields($fields);

        if (empty($this->updates)) {
            return true;
        }

        if ($this->exists) {
            $result = DB::table($this->schema->getName())
                ->where(
                    $this->schema->getAutoIncrementField(),
                    $this->fields[$this->schema->getAutoIncrementField()]
                )->update($this->updates)->execute();
            $this->updates = array();
            return $result;
        } else {
            $id = DB::table($this->schema->getName())->insertGetId($this->updates)->execute();
            if ($id > 0) {
                $this->setField($this->schema->getAutoIncrementField(), $id);
                $this->updates = array();
            }
            return $id;
        }
    }

    public function fields()
    {
        return $this->fields;
    }

    public function isExists()
    {
        return $this->exists;
    }

    public function isModified($field)
    {
        return array_key_exists($field, $this->updates);
    }

    private function parseUpdateFields($fields)
    {
        if (!empty($fields)) {
            foreach ($fields as $key => $value) {
                $this->setField($key, $value);
            }
        }
    }

    private function setField($field, $value)
    {
        if (!$this->schema->existsField($field)) {
            throw new EntityException("Can't find field [$field] in table [" . $this->schema->getName());
        }

        if ($value !== $this->shadows[$field]) {
            if ($this->autoIncrementField === $field) {
                if (!$this->exists) {
                    if (DB::table($this->schema->getName())->where($field, $value)->count()->execute() > 0) {
                        $this->exists = true;
                    }
                    $this->query = DB::table($this->schema->getName())->where($field, $value);
                }
            }
            $this->fields[$field] = $value;
            if ($this->autoIncrementField !== $field) {
                $this->updates[$field] = $value;
            }
            $this->shadows[$field] = $value;
        }
    }

    private function load()
    {
        if (isset($this->query)) {
            $result = $this->query->execute();
            if (!empty($result)) {
                if (count($result) > 1) {
                    throw new EntityException("More than one row in table query.");
                }
                $row = $result[0];
                foreach ($row as $key => $value) {
                    $this->fields[$key] = $value;
                }

                $this->shadows = $row;
                $this->exists = true;
            } else {
                $this->exists = false;
                foreach ($this->schema->columns() as $field) {
                    $this->shadows[$field] = null;
                }
            }
        }
    }

    public function __get($field)
    {
        return !empty($this->fields) && array_key_exists($field, $this->fields) ? $this->fields[$field] : null;
    }

    public function __set($field, $value)
    {
        $this->setField($field, $value);
    }
}