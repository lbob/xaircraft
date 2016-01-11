<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/30
 * Time: 10:09
 */

namespace Xaircraft\Core\Attribute;


class VariableAttribute extends Attribute
{
    private $class;

    private $isArray = false;

    public function initialize($value)
    {
        if (preg_match('#^(?<class>[\\\\\/]?[a-zA-Z][a-zA-Z\_0-9\\\\\/]*)(?<arrayize>\[\])?#i', $value, $match)) {
            $this->class = $match['class'];
            $this->isArray = array_key_exists('arrayize', $match);
        }
    }

    public function isArray()
    {
        return $this->isArray;
    }

    /**
     * @return mixed
     */
    public function invoke()
    {
        return $this->class;
    }
}