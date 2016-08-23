<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/12
 * Time: 16:09
 */

namespace Xaircraft\Database\Func;


class Func
{
    public static function count($field)
    {
        return new CountFieldFunction($field);
    }

    public static function distinct($field)
    {
        return new DistinctFieldFunction($field);
    }

    public static function distance($lngField, $latField, $lng, $lat)
    {
        return new GISDistanceFieldFunction($lngField, $latField, $lng, $lat);
    }

    public static function sum($field)
    {
        return new SumFieldFunction($field);
    }

    public static function convert($field, $type)
    {
        return new ConvertFieldFunction($field, $type);
    }

    public static function isNull($field)
    {
        return new IsNullFieldFunction($field);
    }

    public static function isNotNull($field)
    {
        return new IsNotNullFieldFunction($field);
    }

    public static function max($field)
    {
        return new MaxFieldFunction($field);
    }

    public static function min($field)
    {
        return new MinFieldFunction($field);
    }
}