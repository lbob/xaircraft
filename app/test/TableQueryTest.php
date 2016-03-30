<?php
use Xaircraft\Database\Func\ConvertFieldFunction;
use Xaircraft\Database\Func\Func;
use Xaircraft\Database\OrderInfo;
use Xaircraft\Database\WhereQuery;
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
        //var_dump($query->getQueryString());
    }

    public function testWhereQuery()
    {
        $query = DB::table('user')->select(array(
            'ids' => function (WhereQuery $whereQuery) {
                $whereQuery->select('id')->from('product_definition')->take();
            }
        ));
        //var_dump($query->getQueryString());
    }

    public function testSelectionQuery()
    {
        $query = DB::table('user')->select(array());
        //var_dump($query->getQueryString());
    }

    /**
     *
     */
    public function testOrderFieldFunction()
    {
        $query = DB::table('user')->select()->orderBy(Func::convert('id', ConvertFieldFunction::USING_GBK), OrderInfo::SORT_DESC);
        //var_dump($query->getQueryString());
    }

    public function testUnitModule()
    {
        $count = DB::table('crop')->where('name', 'unit_test')->count()->execute();
        var_dump($count);

        $result = DB::table('crop')->insert(array(
            'name' => 'unit_test'
        ))->execute();
        var_dump($result);
    }
}