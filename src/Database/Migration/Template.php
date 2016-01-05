<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 20:22
 */

namespace Xaircraft\Database\Migration;


class Template
{
    public static function generate($name, $author)
    {
        $content = str_replace('{{date}}', date("Y-m-d H:i:s", time()), self::getTemplate());
        $content = str_replace('{{name}}', $name, $content);
        $content = str_replace('{{author}}', $author, $content);
        return $content;
    }

    private static function getTemplate()
    {
        return "
<?php

/**
 * Date: {{date}}
 * Author: {{author}}
 */
class {{name}} extends \\Xaircraft\\Database\\Migration\\Migration
{

    public function up()
    {
        \$sql = <<<QUERY

QUERY;

        if (false === \\Xaircraft\\DB::statement(\$sql))
            return false;

        return true;
    }

    public function down()
    {
        // TODO: Implement down() method.
    }
}
";
    }
}