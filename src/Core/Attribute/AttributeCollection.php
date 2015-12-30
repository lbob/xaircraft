<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/8
 * Time: 5:35
 */

namespace Xaircraft\Core\Attribute;


class AttributeCollection
{
    private $attributes;

    private function __construct() {}

    public static function create($comment)
    {
        $attributes = AttributeInfo::create($comment);
        $collection = new AttributeCollection();

        if (isset($attributes)) {
            foreach ($attributes as $info) {
                $attribute = Attribute::createFromAttributeInfo($info);
                if (isset($attribute)) {
                    $collection->attributes[] = $attribute;
                }
            }
        }
        return $collection;
    }

    public function invoke()
    {
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                /** @var Attribute $attribute */
                $attribute->invoke();
            }
        }
    }

    public function exists($class)
    {
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                if ($class === get_class($attribute)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function attributes($class = null)
    {
        if (empty($this->attributes)) {
            return null;
        }

        $attributes = array();
        foreach ($this->attributes as $attribute) {
            if ($class === get_class($attribute)) {
                $attributes[] = $attribute;
            }
        }

        return $this->attributes;
    }
}