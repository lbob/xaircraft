<?php
use Account\Group;

/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 2016/3/11
 * Time: 13:45
 */
class BaseTreeTest extends PHPUnit_Framework_TestCase
{
    public function testMakeTree()
    {
        $children = array();
        Group::makeTrees(1, null, null, function ($state, $node) use (&$children) {
            /** @var Group $current */
            $children[] = Group::load($node);
        });
        var_dump($children);
    }
}