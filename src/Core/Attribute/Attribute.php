<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/7
 * Time: 23:07
 */

namespace Xaircraft\Core\Attribute;


abstract class Attribute
{
    public abstract function initialize($value);

    /**
     * @return mixed
     */
    public abstract function invoke();

    public static function createFromAttributeInfo(AttributeInfo $info)
    {
        switch (strtolower($info->type)) {
            case "param":
                $attribute = new ParameterAttribute();
                break;
            case "auth":
                $attribute = new AuthorizeAttribute();
                break;
            case "output_status_exception":
                $attribute = new OutputStatusExceptionAttribute();
                break;
            case "var":
                $attribute = new VariableAttribute();
                break;
        }
        if (isset($attribute)) {
            $attribute->initialize($info->value);
            return $attribute;
        }
        return null;
    }
}