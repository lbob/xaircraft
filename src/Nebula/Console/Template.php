<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/5
 * Time: 10:05
 */

namespace Xaircraft\Nebula\Console;


use Xaircraft\Core\Strings;

class Template
{
    public static function generateModel($table, $class, array $properties, $namespace = null)
    {
        $content = str_replace('{{header}}', self::generateHeader($properties), self::getModelTemplate());
        $namespace = isset($namespace) ? "namespace $namespace;" : "";
        $content = str_replace('{{namespace}}', $namespace, $content);
        $content = str_replace('{{model}}', $class, $content);
        $content = str_replace('{{table_name}}',
            ($table === Strings::camelToSnake($class) ? "" : '
    protected $table = "' . $table . '";
'),
            $content);
        return $content;
    }

    public static function generateProperties(array $properties)
    {
        $statements = array();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                $statements[] = " * @property mixed $property";
            }
        }

        return implode('
', $statements);
    }

    public static function generateHeader(array $properties)
    {
        $content = str_replace('{{property}}', self::generateProperties($properties), self::getModelHeaderTemplate());
        $content = str_replace('{{create_at}}', date("Y-m-d H:i:s", time()), $content);
        return $content;
    }

    private static function getModelHeaderTemplate()
    {
        return '/**
 * Date: {{create_at}}
{{property}}
 */';
    }

    private static function getModelTemplate()
    {
        return '<?php {{namespace}}
use Xaircraft\Nebula\Model;

{{header}}
class {{model}} extends Model
{{{table_name}}
    public function beforeSave()
    {
        // TODO: Implement beforeSave() method.
    }

    public function afterSave($isAppend = false)
    {
        // TODO: Implement afterSave() method.
    }

    public function beforeDelete()
    {
        // TODO: Implement beforeDelete() method.
    }

    public function afterDelete($fields)
    {
        // TODO: Implement afterDelete() method.
    }

    public function beforeForceDelete()
    {
        // TODO: Implement beforeForceDelete() method.
    }

    public function afterForceDelete($fields)
    {
        // TODO: Implement afterForceDelete() method.
    }
}';
    }
}