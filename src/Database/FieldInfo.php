<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 15:31
 */

namespace Xaircraft\Database;


use Xaircraft\Database\Func\FieldFunction;
use Xaircraft\Exception\QueryException;

class FieldInfo
{
    private $name;

    private $alias;

    private $field;

    private $prefix;

    /**
     * @var FieldFunction
     */
    private $func;

    public $queryHandler;

    private $value;

    private $isSubQueryField = false;

    public static function make($name, $alias = null, $queryHandler = null, $isSubQueryField = false)
    {
        $field = new FieldInfo();
        if ($name instanceof FieldFunction) {
            $field->func = $name;
            $field->field = $name->field;
        } else {
            $field->name = trim($name);
        }
        $field->alias = isset($alias) ? trim($alias) : null;
        $field->queryHandler = $queryHandler;
        $field->isSubQueryField = $isSubQueryField;

        $field->parseName();

        return $field;
    }

    public static function makeValueColumn($alias, $value)
    {
        $field = new FieldInfo();
        $field->alias = $alias;
        $field->value = $value;

        return $field;
    }

    public function setSubQueryField()
    {
        $this->isSubQueryField = true;
    }

    private function parseName()
    {
        if (isset($this->name)) {
            if (preg_match('#^[a-zA-Z][a-zA-Z0-9\_\-]*$#i', $this->name)) {
                $this->field = $this->name;
            }
            if (preg_match('#^(?<prefix>[a-zA-Z][a-zA-Z0-9\_\-]*)\.(?<field>[a-zA-Z][a-zA-Z0-9\_\-]*)$#i', $this->name, $match)) {
                $this->prefix = $match['prefix'];
                $this->field = $match['field'];
            }
            if (preg_match('#[ ]+[aA][sS][ ]+#i', $this->name, $match)) {
                throw new QueryException("Field's alias pls use array('{{field}}' => '{{alias}}')");
            }
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getValueColumnSymbol()
    {
        return "$this->value AS $this->alias";
    }

    public function getName(QueryContext $context)
    {
        if (isset($this->func)) {
            $field = $this->func->getString($context);
        } else {
            $field = $context->getField($this->field, $this->prefix, $this->isSubQueryField);
        }
        if (isset($this->alias)) {
            $field = "$field AS $this->alias";
        }
        return $field;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}