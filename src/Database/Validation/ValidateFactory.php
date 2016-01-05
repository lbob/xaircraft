<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 10:53
 */

namespace Xaircraft\Database\Validation;


use Xaircraft\Exception\QueryException;

class ValidateFactory
{
    public static function makeCollections($expression)
    {
        if (!isset($expression) || !is_string($expression)) {
            throw new QueryException("Validation expression error. [$expression]");
        }

        if (preg_match_all('#\<\!\-\-(?<type>[A-Z]+)\:(?<expression>.+?)\-\-\>#i', $expression, $matches, PREG_SET_ORDER)) {
            $collections = new ValidationCollection();
            foreach ($matches as $match) {
                $type = $match['type'];
                $expression = $match['expression'];
                $item = self::makeValidateInfo($type, $expression);

                if (isset($item)) {
                    $collections->append($item);
                }
            }

            return $collections;
        }

        return null;
    }

    private static function makeValidateInfo($type, $expression)
    {
        switch (strtolower($type)) {
            case 'reg':
            case 'regex':
                return new RegexValidateInfo($expression);
            case 'range':
                return new RangeValidateInfo($expression);
            default:
                return null;
        }
    }
}