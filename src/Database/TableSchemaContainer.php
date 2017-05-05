<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/5/5
 * Time: 10:41
 */

namespace Xaircraft\Database;


class TableSchemaContainer
{
    protected $schemas;

    public function __construct()
    {
        $this->schemas = array();
    }

    public function get($name)
    {
        if (!isset($this->schemas[$name])) {
            $this->schemas[$name] = new TableSchema($name);
        }
        return $this->schemas[$name];
    }

    public function set($name, $obj)
    {
        $this->schemas[$name] = $obj;
    }
}