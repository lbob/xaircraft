<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/16
 * Time: 17:03
 */

namespace Xaircraft\Core;


use Xaircraft\Core\Attribute\Attribute;
use Xaircraft\Core\Attribute\AttributeCollection;
use Xaircraft\Core\Attribute\VariableAttribute;
use Xaircraft\DI;
use Xaircraft\Nebula\Model;

class Json
{
    public static function toObject($arg, $class)
    {
        if (is_string($class)) {
            $class = new \ReflectionClass($class);
        }

        $params = $arg;
        if (is_string($arg) && !is_array($arg)) {
            $params = self::toArray($arg);
        }
        if (!empty($params)) {
            $object = DI::get($class->name);
            if ($object instanceof Model) {
                $object = $object->load($params);
            } else {
                $properties = $class->getProperties();
                if (!empty($properties)) {
                    foreach ($properties as $property) {
                        if (array_key_exists($property->name, $params)) {
                            $value = $params[$property->name];
                            $attributes = AttributeCollection::create($property->getDocComment())
                                ->attributes(VariableAttribute::class);
                            if (!empty($attributes[0])) {
                                /** @var Attribute $attribute */
                                $attribute = $attributes[0];
                                $class = $attribute->invoke();
                                if (isset($class) && false === array_search(strtolower($class), array(
                                        "string", "int", "boolean", "bool", "array", "double", "float", "resource"
                                    ))) {
                                    $value = self::toObject($value, $class);
                                }
                            }
                            $property->setValue($object, $value);
                        }
                    }
                }
            }
            return $object;
        }
        return null;
    }

    public static function toArray($json, $class = null)
    {
        $list = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Json decode error.[$json]");
        }

        if (isset($class)) {
            $result = array();
            foreach ($list as $item) {
                $result[] = self::toObject($item, $class);
            }
            return $result;
        }
        return $list;
    }
}