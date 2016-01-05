<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 17:42
 */

namespace Xaircraft\Database\Migration;


abstract class Migration
{
    public abstract function up();

    public abstract function down();
}