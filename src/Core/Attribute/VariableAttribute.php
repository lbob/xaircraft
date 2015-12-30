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

    public function initialize($value)
    {
        if (preg_match('#^[\\\\\/]?[a-zA-Z][a-zA-Z\_0-9\\\\\/]*$#i', $value)) {
            $this->class = $value;
        }
    }

    /**
     * @return mixed
     */
    public function invoke()
    {
        return $this->class;
    }
}