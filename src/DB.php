<?php

namespace Xaircraft;
use Xaircraft\Database\PdoDatabase;
use Xaircraft\Exception\DatabaseException;


/**
 * Class DB
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/25 10:16
 */
class DB {

    /**
     * @var array
     */
    private static $instances;

    private static $currentDatabase = 'default';

    /**
     * @var \Xaircraft\Database\Database
     */
    protected $provider;

    private function __construct(\Xaircraft\Database\Database $provider, $database)
    {
        $this->provider = $provider;

        $configs = require App::path('config') . '/database.php';

        if (isset($configs) && !empty($configs)) {
            if (!array_key_exists($database, $configs)) {
                throw new DatabaseException("找不到数据库配置 [$database]");
            }

            $config = $configs[$database];

            if (!isset($config) || !is_array($config) || empty($config))
                throw new DatabaseException("Database config undefined.");
            if (!array_key_exists('driver', $config) || !isset($config['driver']))
                throw new DatabaseException("Database config must include driver.");
            else
                $dsn[] = $config['driver'] . ':';
            if (!array_key_exists('database', $config) || !isset($config['database']))
                throw new DatabaseException("Database config must include database name.");
            else
                $dsn[] = 'dbname=' . $config['database'] . ';';
            if (!array_key_exists('host', $config) || !isset($config['host']))
                throw new DatabaseException("Database config must include host.");
            else
                $dsn[] = 'host=' . $config['host'] . ';';
            if (array_key_exists('charset', $config) && isset($config['charset']))
                $dsn[] = 'charset=' . $config['charset'] . ';';
            if (array_key_exists('collation', $config) && isset($config['collation']))
                $dsn[] = 'collation=' . $config['collation'] . ';';
            $dsn = implode('', $dsn);
            if (!array_key_exists('username', $config) || !isset($config['username']))
                throw new DatabaseException("Database config must include username.");
            else
                $username = $config['username'];
            if (!array_key_exists('password', $config) || !isset($config['password']))
                throw new DatabaseException("Database config must include password.");
            else
                $password = $config['password'];
            $prefix = null;
            if (array_key_exists('prefix', $config) && isset($config['prefix']))
                $prefix = $config['prefix'];

            $this->provider->connection($dsn, $username, $password, null, $config['database'], $prefix);
        } else {
            throw new DatabaseException("数据库配置错误");
        }
    }

    /**
     * @param string $database
     * @return DB
     */
    private static function getInstance($database)
    {
        if (!isset(self::$instances) || !array_key_exists($database, self::$instances) || self::$currentDatabase !== $database) {
            self::$instances[$database] = self::create(App::environment(Globals::ENV_DATABASE_PROVIDER), $database);
            self::$currentDatabase = $database;
        }
        return self::$instances[$database];
    }

    private static function create($provider, $database)
    {
        switch ($provider) {
            case Globals::DATABASE_PROVIDER_PDO:
                return new DB(new PdoDatabase(), $database);
            default:
                return new DB(new PdoDatabase(), $database);
        }
    }

    /**
     * 执行 Select 查询
     * @param $query String 查询语句
     * @return array 返回查询结果的数组
     */
    public static function select($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->select($query, $params);
    }

    /**
     * 执行 Insert 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function insert($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->insert($query, $params);
    }

    /**
     * 执行 Delete 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function delete($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->delete($query, $params);
    }

    /**
     * 执行 Update 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function update($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->update($query, $params);
    }

    /**
     * 执行非CRUD操作
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function statement($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->statement($query, $params);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    public static function query($query, array $params = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->query($query, $params);
    }

    /**
     * 执行一个事务过程，在$handler中抛出异常则将自动执行回滚
     * @param callable $handler
     * @return mixed
     */
    public static function transaction(callable $handler)
    {
        return self::getInstance(self::$currentDatabase)->provider->transaction($handler);
    }

    /**
     * 手动开始一个事务过程
     * @return mixed
     */
    public static function beginTransaction()
    {
        self::getInstance(self::$currentDatabase)->provider->beginTransaction();
    }

    /**
     * 手动回滚一个事务过程
     * @return mixed
     */
    public static function rollback()
    {
        self::getInstance(self::$currentDatabase)->provider->rollback();
    }

    /**
     * 手动提交事务查询
     * @return mixed
     */
    public static function commit()
    {
        self::getInstance(self::$currentDatabase)->provider->commit();
    }

    /**
     * 禁用查询记录功能
     * @return mixed
     */
    public static function disableQueryLog()
    {
        self::getInstance(self::$currentDatabase)->provider->disableQueryLog();
    }

    /**
     * 获得查询语句
     * @return mixed
     */
    public static function getQueryLog()
    {
        $logs = array();
        if (isset(self::$instances) && !empty(self::$instances)) {
            foreach (self::$instances as $instance) {
                $logs[$instance->provider->getDatabaseName()] = $instance->provider->getQueryLog();
            }
        }
        return $logs;
    }

    /**
     * 建立一个连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @param $database
     * @param $prefix
     * @return mixed
     */
    public static function connection($dsn, $username, $password, $options, $database = null, $prefix = null)
    {
        self::getInstance(self::$currentDatabase)->provider->connection($dsn, $username, $password, $options, $database, $prefix);
    }

    /**
     * 关闭现有连接
     * @return mixed
     */
    public static function disconnect()
    {
        self::getInstance(self::$currentDatabase)->provider->disconnect();
    }

    /**
     * 重新建立新的连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @param $database
     * @param $prefix
     * @return mixed
     */
    public static function reconnect($dsn, $username, $password, $options, $database = null, $prefix = null)
    {
        self::getInstance(self::$currentDatabase)->provider->reconnect($dsn, $username, $password, $options, $database, $prefix);
    }

    /**
     * 获得数据库驱动对象
     * @return \PDO 返回数据库驱动对象
     */
    public static function getDbDriver()
    {
        return self::getInstance(self::$currentDatabase)->provider->getDbDriver();
    }

    /**
     * @param null $name
     * @return mixed
     */
    public static function lastInsertId($name = null)
    {
        return self::getInstance(self::$currentDatabase)->provider->lastInsertId($name);
    }

    /**
     * 获得数据表查询对象
     * @param String $tableName 数据表名称
     * @return \Xaircraft\Database\TableQuery
     */
    public static function table($tableName)
    {
        return self::getInstance(self::$currentDatabase)->provider->table($tableName);
    }

    /**
     * @param $query
     * @return \Xaircraft\Nebula\Entity
     */
    public static function entity($query)
    {
        return self::getInstance(self::$currentDatabase)->provider->entity($query);
    }

    /**
     * @param $tempTableName
     * @param callable $handler
     * @return \Xaircraft\Database\TempTableQuery
     */
    public static function temptable($tempTableName, callable $handler)
    {
        return self::getInstance(self::$currentDatabase)->provider->temptable($tempTableName, $handler);
    }

    /**
     * 获得上一次执行产生的错误代码
     * @return string
     */
    public static function errorCode()
    {
        return self::getInstance(self::$currentDatabase)->provider->errorCode();
    }

    /**
     * 获得上一次执行产生的错误信息
     * @return array
     */
    public static function errorInfo()
    {
        return self::getInstance(self::$currentDatabase)->provider->errorInfo();
    }

    /**
     * @param string $value
     * @return \Xaircraft\Database\Raw
     */
    public static function raw($value)
    {
        return self::getInstance(self::$currentDatabase)->provider->raw($value);
    }

    /**
     * 创建数据库表构造器
     * @return \Xaircraft\Database\Table
     */
    public static function schema()
    {
        return self::getInstance(self::$currentDatabase)->provider->schema();
    }

    /**
     * @param $database
     * @return Database\Database
     */
    public static function database($database)
    {
        return self::getInstance($database)->provider;
    }

    /**
     * 获取数据库配置节点名称
     * @return string
     */
    public static function getDatabaseName()
    {
        return self::getInstance(self::$currentDatabase)->provider->getDatabaseName();
    }
}

 