<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/7
 * Time: 17:50
 */

namespace Xaircraft\Queue;


class JobCollection
{
    public $collection;

    public function __construct()
    {
        $this->collection = array();
    }
}