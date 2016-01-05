<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 10:27
 */

namespace Xaircraft\Database\Validation;


class RegexValidateInfo implements Validate
{
    private $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function valid($value)
    {
        return preg_match("#$this->pattern#i", $value);
    }
}