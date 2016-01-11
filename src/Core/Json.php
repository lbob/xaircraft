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
            try {
                $params = self::toArray($arg);
            } catch (\Exception $ex) {
                return $params;
            }
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
                                /** @var VariableAttribute $attribute */
                                $attribute = $attributes[0];
                                $class = $attribute->invoke();
                                if ($attribute->isClass() && isset($class)) {
                                    if ($attribute->isArray()) {
                                        $value = self::toArray($value, $class);
                                    } else {
                                        $value = self::toObject($value, $class);
                                    }
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

    public static function toArray($arg, $class = null)
    {
        if (is_string($arg) && !is_array($arg)) {
            $list = json_decode($arg, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \Exception("Json decode error.[$arg]");
            }
        } else {
            $list = $arg;
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