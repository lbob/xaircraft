<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 10:33
 */

namespace Xaircraft\Database\Validation;


use Xaircraft\Exception\DataTableException;
use Xaircraft\Exception\FieldValidateException;
use Xaircraft\Exception\QueryException;

class RangeValidateInfo implements Validate
{
    private $min;

    private $includeMin = false;

    private $max;

    private $includeMax = false;

    public function __construct($expression)
    {
        if (!isset($expression) || !is_string($expression)) {
            throw new QueryException("Invalid expression in RangeValidateInfo. [$expression]");
        }
        if (preg_match('#(?<head>[\(\[])(?<min>[0-9\.]+),[ ]*(?<max>[0-9\.]+)(?<tail>[\)\]])#i', $expression, $matches)) {
            $head = $matches['head'];
            $tail = $matches['tail'];
            $this->min = $matches['min'];
            $this->max = $matches['max'];

            $this->includeMin = ('[' === $head);
            $this->includeMax = (']' === $tail);
        }
    }

    public function valid($value)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $expression = $value;
        $expression .= $this->includeMin ? ">=" : ">";
        $expression .= $this->min;
        $expression .= "&&";
        $expression .= $value;
        $expression .= $this->includeMax ? "<=" : "<";
        $expression .= $this->max;

        return eval("return ($expression);");
    }
}