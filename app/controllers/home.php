<?php
use Xaircraft\Async\Job;
use Xaircraft\Authentication\Auth;
use Xaircraft\Authentication\Contract\CurrentUser;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Json;
use Xaircraft\Core\Strings;
use Xaircraft\Database\Data\FieldFormatter;
use Xaircraft\Database\Data\FieldType;
use Xaircraft\Database\Func\Func;
use Xaircraft\Database\WhereQuery;
use Xaircraft\DB;
use Xaircraft\DI;
use Xaircraft\Exception\ModelException;
use Xaircraft\Extensions\Log\Log;
use Xaircraft\Nebula\Model;
use Xaircraft\Queue\QueueContext;
use Xaircraft\Queue\TaskQueue;
use Xaircraft\Web\Mvc\Controller;
use Xaircraft\Web\Mvc\OutputStatusException;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 16:55
 */
class home_controller extends Controller implements OutputStatusException
{
    /*
     * GET /home/account/123456
     */
    public function get_account($guid)
    {
        var_dump($_SERVER);
    }

    /**
     * PUT /home/account/
     */
    public function put_account()
    {

    }

    /*
     *
     */
    public function delete_account($guid)
    {

    }
}