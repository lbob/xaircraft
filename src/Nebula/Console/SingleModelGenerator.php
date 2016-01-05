<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 8:29
 */

namespace Xaircraft\Nebula\Console;


use Xaircraft\Console\Console;
use Xaircraft\Core\IO\File;
use Xaircraft\Core\Strings;
use Xaircraft\Database\TableSchema;

class SingleModelGenerator extends ModelCommandExecutor
{
    public function handle()
    {
        $this->checkArgs(array('table'));

        $namespace = array_key_exists('namespace', $this->args) ? $this->args['namespace'] : null;
        if (!isset($namespace)) {
            $namespace = array_key_exists('ns', $this->args) ? $this->args['ns'] : null;
        }
        $table = $this->args['table'];
        $class = array_key_exists('class', $this->args) ? $this->args['class'] : Strings::snakeToCamel($table);
        $path = $this->path($class, $namespace);
        $schema = new TableSchema($table);

        if (file_exists($path)) {
            $content = File::readText($path);
            $className = isset($namespace) ? "$namespace\\$class" : $class;
            $reflection = new \ReflectionClass($className);
            $header = $reflection->getDocComment();
            if (isset($header)) {
                $content = str_replace($header, Template::generateHeader($schema->columns()), $content);
                unlink($path);
                File::writeText($path, $content);
            }
            Console::line("Model [$class] updated in path [$path].");
        } else {
            File::writeText($path,
                Template::generateModel($table, $class, $schema->columns(), $namespace)
            );
            Console::line("Model [$class] created in path [$path].");
        }
    }
}