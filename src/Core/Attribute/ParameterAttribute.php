<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/15
 * Time: 15:03
 */

namespace Xaircraft\Core\Attribute;


class ParameterAttribute extends Attribute
{
    const METHOD_POST = "post";
    const METHOD_GET = "get";

    private $name;

    private $type;

    private $method;

    public function initialize($value)
    {
        if (preg_match('#^(?<type>[a-zA-Z][a-zA-Z0-9\_\-]*)[ ]+\$(?<name>[a-zA-Z][a-zA-Z0-9\_\-]*)([ ]+(?<method>([a-zA-Z]+)))?$#i', $value, $match)) {
            $this->type = array_key_exists('type', $match) ? $match['type'] : null;
            $this->name = array_key_exists('name', $match) ? $match['name'] : null;
            $this->method = array_key_exists('method', $match) ? $match['method'] : null;

            if (!isset($this->method)) {
                $this->method = self::METHOD_GET;
            }
        }
    }

    /**
     * @return mixed
     */
    public function invoke()
    {
        // TODO: Implement invoke() method.
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function isGet()
    {
        return self::METHOD_GET === strtolower($this->method);
    }

    public function isPost()
    {
        return self::METHOD_POST === strtolower($this->method);
    }

    public static function get(AttributeCollection $attributeCollection, $name)
    {
        $attributes = $attributeCollection->attributes(ParameterAttribute::class);
        if (!empty($attributes)) {
            /** @var ParameterAttribute $attribute */
            foreach ($attributes as $attribute) {
                if ($attribute instanceof ParameterAttribute && $name === $attribute->name) {
                    return $attribute;
                }
            }
        }
        return null;
    }
}