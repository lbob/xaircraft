<?php
use Account\User;
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
use Xaircraft\Nebula\Model;
use Xaircraft\Web\Mvc\Argument\Post;
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
    public function __construct()
    {
        DB::database('agri_data_center');
    }

    public function test_result()
    {
        $result = DB::table('user')->where('id', 2000)->select()->execute();
        var_dump($result);
        return $this->status('SUCCESS', \Xaircraft\Globals::STATUS_SUCCESS, $result);
    }

    /**
     * @param $id
     * @param $title
     * @return \Xaircraft\Web\Mvc\Action\TextResult
     * @output_status_exception
     */
    public function index($id = 2, $title = 'sdfsf', array $test = array())
    {
        var_dump($id);
        var_dump($title);
        var_dump($test);

        $query = \Xaircraft\DB::table('user AS u')->select()->join('product AS p', 'p.id', 'u.id')->where('p.id', '>', 0);
        //$query = \Xaircraft\DB::table('user AS u')->select('u.id')->join('project AS p', 'p.id', 'u.id')->where('u.id', '>', 0);
//        $query = \Xaircraft\DB::table('user')->select('name')->whereIn('id', function (WhereQuery $whereQuery) {
//            $whereQuery->select('name')->from('company')->where('id', 9);
//        })->groupBy('user.id', 'user.name')->having('user.id', 0);
//        $query = DB::table('user')->update(array(
//            'name' => '5',
//            'password' => 'adf',
//            'level' => 'admin'
//        ))->where('id', 9);

        //$result = $query->execute();
        $queryString = $query->getQueryString();

        //var_dump($result);
        var_dump($queryString);

        var_dump(DB::getQueryLog());


    }

    public function test_format()
    {
        $query = DB::table('user')->select(array(
            'create_at' => 1
        ))->format(array(
            'create_at' => FieldFormatter::create(FieldType::DATE, 'Y年m月d日')
        ))->execute();
        var_dump($query);
    }

    public function test_sum()
    {
        $query = DB::table('user')->select(array(
            'total_count' => Func::sum('id')
        ))->pluck()->execute();
        var_dump($query);
    }

    /**
     * @param array $messages
     */
    public function test_arg(array $messages = null)
    {
        $query = DB::table('user')->select(array(
            'create_by' => function (WhereQuery $whereQuery) {
                $whereQuery->select('username')->from('user')->where('id', 2);
            }
        ))->execute();
        var_dump($query);
        var_dump(DB::getQueryLog());
    }

    public function test_sub_query_func()
    {
        $query = DB::table('user')->whereIn('id', function (WhereQuery $whereQuery) {
            $whereQuery->select()->from('product')->where(Func::sum('user.id'), 0);
        })->select(Func::sum('user.id'));
        var_dump($query->getQueryString());
    }

    public function test_sub_query()
    {
        $query = DB::table('user')->whereExists(function (WhereQuery $whereQuery) {
            $whereQuery->select()->from('product')->where(Func::sum('user.id'), DB::raw('id'));
        })->select();
        var_dump($query->getQueryString());
    }

    public function test_query_func()
    {
        $lng = 0;
        $lat = 0;

        DB::database('agri_data_center');
        $list = DB::table('user')
            ->select()
            ->where(Func::distance("id", "name", $lng, $lat), 0)
            ->getQueryString();
        var_dump($list);
    }

    public function test_where()
    {
        $query = DB::table('user')->where(function (WhereQuery $whereQuery) {
            $whereQuery->where('id', '>', 0)
                ->orWhere('name', 'LIKE', 'test');
        })->select();

        var_dump($query->execute());
    }

    /**
     * @throws \Xaircraft\Exception\ModelException
     * @output_status_exception
     */
    public function test_model()
    {
        $value = 0;
        var_dump($value == null);
        /** @var User $user */
        $user = User::model();
        $user->name = "3";
        $user->password = "asdf";
        $user->level = "normal";
        var_dump($user);
        $user->save();

        var_dump($user->isModified("name"));

        $user->name = "4";
        $user->level = "normal";

        var_dump($user->isModified("name"));
        var_dump($user->isModified("level"));

        $user->save();

        var_dump(DB::getQueryLog());
    }

    public function test_trait()
    {
        User::children(0, array());
    }

    public function test_single()
    {
        $list = DB::table('user')->select('create_at')->single()->format(array(
            'create_at' => FieldType::DATE
        ))->execute();
        var_dump($list);
    }

    public function test_detail()
    {
        $query = DB::table('user')->select()->take(1)->detail();

        $detail = $query->execute();
        var_dump($detail);

        $detail = $query->execute();
        var_dump($detail);
    }

    public function test_model_load()
    {
        $user = User::load(array(
            "id" => 168,
            "name" => "3",
            "password" => "asdf",
            "level" => "admin"
        ));
        $user->save();

        var_dump(DB::getQueryLog());
    }

    /**
     * @param array $ids post
     * @param Message $message
     * @param $id
     */
    public function test_model_exists(array $ids = null, Message $message, $id)
    {
        var_dump($id);
        var_dump($ids);
        var_dump($message);
    }

    public function test_query()
    {
        $list = DB::table('user')->select(array(
            "count" => function (WhereQuery $whereQuery) {
                $whereQuery->count()->from('user');
            }
        ))->single()->execute();

        var_dump($list);
    }

    public function test_json()
    {
        $message = Json::toArray(
            '[{"hellos":[{"id":1,"name":"asdfasdf"},{"id":2,"name":"asdfasdf2"}],"id":234234,"content":"hello","contract":{"sender":"test","to":"to_test","message":{"id":12,"content":"hello","contract":{"sender":"test","to":"to_test"}}}},{"id":12,"content":"hello","contract":{"sender":"test","to":"to_test","message":{"id":12,"content":"hello","contract":{"sender":"test","to":"to_test"}}}}]',
            Message::class
        );
        var_dump($message);

        $list = Json::toArray("[1,2,3,4,5,6]");
        var_dump($list);
    }

    public function test_order()
    {
        $query = DB::table('user')->orderBy('id', \Xaircraft\Database\OrderInfo::SORT_ASC)->select(array(
            "id", "name",
            "project_id" => function (WhereQuery $whereQuery) {
                $whereQuery->from('project')->select('id')->top();
            }
        ))->execute();
    }

    public function test_coroutines()
    {
        $gen = $this->gen();
        var_dump([$gen->current(), $gen->key()]);
        var_dump($gen->send('ret1'));
        var_dump($gen->send('ret2'));
    }

    function gen()
    {
        $ret = (yield 'key1' => 'yield1');
        var_dump($ret);
        $ret = (yield 'yield2');
        var_dump($ret);
    }

    public function test_coroutines2()
    {
        $logger = $this->logger(__DIR__ . '/log');
        $logger->send('foo');
        $logger->send('bar');
    }

    function logger($fileName)
    {
        $fileHandle = fopen($fileName, 'a');
        while (true) {
            fwrite($fileHandle, yield . "\n");
        }
    }

    public function test_closure()
    {
        $task = new Task(function () {
            var_dump('hello');
        });
        $this->closure_dump($task->getClosure());
    }

    private function closure_dump($closure)
    {
        try {
            $func = new ReflectionFunction($closure);
        } catch (ReflectionException $e) {
            echo $e->getMessage();
            return;
        }
        $start = $func->getStartLine() - 1;
        $end = $func->getEndLine() - 1;
        $filename = $func->getFileName();
        echo implode("", array_slice(file($filename), $start, $end - $start + 1));
    }

    public function test_job()
    {
        $job = new HelloJob();
        Job::push($job);
    }

    public function test_trace_dir()
    {
        \Xaircraft\Core\IO\Directory::traceDir('D:\xampp\htdocs\sites\agri\framework\trunk\xaircraft\app\cache', function ($dir, $file) {
            var_dump($dir . '/' . $file);
        });
    }
}