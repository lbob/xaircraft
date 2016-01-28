<?php
use Xaircraft\DB;

/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16-1-28
 * Time: 下午5:37
 */
class TableQueryTest extends PHPUnit_Framework_TestCase
{
    public function testHello()
    {
        $this->assertTrue(true);
    }

    public function testWhereCondition()
    {
        $query = DB::table('user')->where('id', '<>', 0)->select();
        var_dump($query->getQueryString());
        ob_flush();
    }
}