<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 15:10
 */

namespace Xaircraft\Nebula;


use Xaircraft\Core\Collections\Generic;
use Xaircraft\Database\TableQuery;
use Xaircraft\Database\TableSchema;
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
     * 不进行更新的字段
     * @var array
     */
    private $forgets = array();

    /**
     * 强制更新的字段，即使字段值没有更新
     * @var array
     */
    private $forces = array();

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
                throw new EntityException("Must be selection table query. [" . $this->schema->getName() . "]");
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

        if (!empty($this->forgets)) {
            $updates = $this->updates;
            foreach ($this->updates as $key => $value) {
                if (array_search($key, $this->forgets) !== false) {
                    unset($updates[$key]);
                }
            }
            $this->updates = $updates;
        }

        if (empty($this->updates)) {
            return true;
        }

        if ($this->exists) {
            if ($this->schema->existsField(TableSchema::RESERVED_FIELD_UPDATE_AT) &&
                !array_key_exists(TableSchema::RESERVED_FIELD_UPDATE_AT, $this->updates)) {
                $updateAt = time();
                $this->updates[TableSchema::RESERVED_FIELD_UPDATE_AT] = $updateAt;
                $this->fields[TableSchema::RESERVED_FIELD_UPDATE_AT] = $updateAt;
            }
            $result = DB::table($this->schema->getName())
                ->where(
                    $this->schema->getAutoIncrementField(),
                    $this->fields[$this->schema->getAutoIncrementField()]
                )->update($this->updates)->execute();
            $this->updates = array();
            return $result;
        } else {
            $updateAt = time();
            if ($this->schema->existsField(TableSchema::RESERVED_FIELD_CREATE_AT) &&
                !array_key_exists(TableSchema::RESERVED_FIELD_CREATE_AT, $this->updates)) {
                $this->updates[TableSchema::RESERVED_FIELD_CREATE_AT] = time();
                $this->fields[TableSchema::RESERVED_FIELD_CREATE_AT] = $updateAt;
            }
            if ($this->schema->existsField(TableSchema::RESERVED_FIELD_UPDATE_AT) &&
                !array_key_exists(TableSchema::RESERVED_FIELD_UPDATE_AT, $this->updates)) {
                $this->updates[TableSchema::RESERVED_FIELD_UPDATE_AT] = time();
                $this->fields[TableSchema::RESERVED_FIELD_UPDATE_AT] = $updateAt;
            }
            $id = DB::table($this->schema->getName())->insertGetId($this->updates)->execute();
            if ($id > 0) {
                $this->setField($this->schema->getAutoIncrementField(), $id);
                $this->updates = array();
            }
            return $id;
        }
    }

    public function fields(array $fieldFilter = null)
    {
        return Generic::array_key_filter($this->fields, $fieldFilter, true);
    }

    public function forget($field)
    {
        $this->forgets[] = $field;
    }

    public function force($field)
    {
        $this->forces[] = $field;
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

        $forces = $this->forces;
        if ($value !== $this->shadows[$field] || (!empty($forces) && array_search($field, $forces) !== false)) {
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
        $value = !empty($this->fields) && array_key_exists($field, $this->fields) ? $this->fields[$field] : null;
        if (!isset($value)) {
            if ($this->autoIncrementField == $field) {
                $value = 0;
            }
        }
        return $value;
    }

    public function __set($field, $value)
    {
        $this->setField($field, $value);
    }
}