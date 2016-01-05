<?php
use Account\User;
use Xaircraft\Async\Job;
use Xaircraft\Authentication\Auth;
use Xaircraft\Authentication\Contract\CurrentUser;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Json;
use Xaircraft\Core\Strings;
use Xaircraft\Database\Data\FieldType;
use Xaircraft\Database\Func\Func;
use Xaircraft\Database\WhereQuery;
use Xaircraft\DB;
use Xaircraft\DI;
use Xaircraft\Nebula\Model;
use Xaircraft\Web\Mvc\Argument\Post;
use Xaircraft\Web\Mvc\Controller;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/12
 * Time: 16:55
 * @auth LoginAuthorize
 * @auth LoginAuthorize2(userID=123, permission='admin;normal.aa')
 */
class home_controller extends Controller
{
    /**
     * @param $id
     * @param $title
     * @return \Xaircraft\Web\Mvc\Action\TextResult
     */
    public function index($id, $title)
    {
        var_dump($id);
        var_dump($title);

        $query = \Xaircraft\DB::table('user AS u')->select('u.id')->join('project AS p', 'p.id', 'u.id')->where('p.id', '>', 0);
        //$query = \Xaircraft\DB::table('user AS u')->select('u.id')->join('project AS p', 'p.id', 'u.id')->where('u.id', '>', 0);
//        $query = \Xaircraft\DB::table('user')->select('name')->whereIn('id', function (WhereQuery $whereQuery) {
//            $whereQuery->select('u.id')->from('user AS u')->where('u.id', 9);
//        })->groupBy('user.id', 'user.name')->having('user.id', 0);
//        $query = DB::table('user')->update(array(
//            'name' => '5',
//            'password' => 'adf',
//            'level' => 'admin'
//        ))->where('id', 9);

        $result = $query->execute();
        $queryString = $query->getQueryString();

        var_dump($result);
        var_dump($queryString);

        var_dump(DB::getQueryLog());
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
        $user = \Account\User::model();
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