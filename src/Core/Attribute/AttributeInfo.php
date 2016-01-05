<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/7
 * Time: 18:34
 */

namespace Xaircraft\Core\Attribute;


class AttributeInfo
{
    public $type;

    public $value;

    public function __construct($type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public static function create($comment)
    {
        //TODO: Value parser still have some bug, must improve on it.20151208
        if (preg_match_all('#@(?<type>[a-zA-Z][a-zA-Z0-9\_]+)([ ]+(?<value>.*))?#i', $comment, $matches, PREG_SET_ORDER)) {
            $attributes = array();
            foreach ($matches as $match) {
                $type = isset($match['type']) ? $match['type'] : null;
                if (isset($type)) {
                    $value = isset($match['value']) ? $match['value'] : null;
                    $attributes[] = new AttributeInfo($type, trim($value));
                }
            }
            return $attributes;
        }
        return null;
    }
}