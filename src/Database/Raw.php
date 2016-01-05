<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/15
 * Time: 16:30
 */

namespace Xaircraft\Database;


/**
 * Class Raw
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/15 12:14
 */
class Raw {

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

