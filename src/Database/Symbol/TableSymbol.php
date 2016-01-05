<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/12
 * Time: 11:30
 */

namespace Xaircraft\Database\Symbol;


use Xaircraft\Exception\QueryException;

class TableSymbol
{
    private $name;
    private $alias;
    private $sourceSymbol;

    public static function create($symbol)
    {
        if (!isset($symbol) || "" === $symbol) {
            throw new QueryException("Invalid Table Symbol [$symbol].");
        }
        $obj = new TableSymbol();
        $obj->sourceSymbol = trim($symbol);

        $obj->parseSymbol();

        return $obj;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getSymbol()
    {
        if (isset($this->alias)) {
            return "`$this->name` AS `$this->alias`";
        }
        return "`$this->name`";
    }

    public function getPrefix($withUnquote = true)
    {
        if (isset($this->alias)) {
            $prefix = $this->alias;
        } else {
            $prefix = $this->name;
        }
        return $withUnquote ? "`$prefix`" : $prefix;
    }

    public function getSourceSymbol()
    {
        return $this->sourceSymbol;
    }

    private function parseSymbol()
    {
        if (preg_match('#^(?<name>[a-zA-Z][a-zA-Z0-9\_\-]*)([ ]+[aA][sS][ ]+(?<alias>[a-zA-Z][a-zA-Z0-9\_\-]*))?$#i', $this->sourceSymbol, $match)) {
            $this->name = $match['name'];
            $this->alias = array_key_exists('alias', $match) ? $match['alias'] : null;
        } else {
            throw new QueryException("Invalid Table Symbol [$this->sourceSymbol].");
        }
    }
}