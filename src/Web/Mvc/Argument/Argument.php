<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/15
 * Time: 14:25
 */

namespace Xaircraft\Web\Mvc\Argument;


use ReflectionParameter;
use Xaircraft\Core\Attribute\AttributeCollection;
use Xaircraft\Core\Attribute\ParameterAttribute;
use Xaircraft\Core\Json;
use Xaircraft\DI;
use Xaircraft\Exception\ArgumentInvalidException;
use Xaircraft\Nebula\Model;

abstract class Argument
{
    protected $value;

    protected $name;

    protected $reflectionParameter;

    public function __construct($name, $value, ReflectionParameter $reflectionParameter)
    {
        $this->value = $value;
        $this->name = $name;
        $this->reflectionParameter = $reflectionParameter;

        $this->initialize();
    }

    private function initialize()
    {
        if ($this->reflectionParameter->isArray()) {
            $this->value = Json::toArray($this->value);
        }
        if (!isset($this->value)) {
            if ($this->reflectionParameter->isOptional()) {
                $defaultValue = $this->reflectionParameter->getDefaultValue();
                $this->value = $defaultValue;
            }
        }
        $class = $this->reflectionParameter->getClass();
        if (isset($class)) {
            $this->value = Json::toObject($this->value, $class);
        }

        if (!$this->reflectionParameter->allowsNull() && !isset($this->value)) {
            throw new ArgumentInvalidException($this->name, "Argument [$this->name] can't be null.");
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function createArgs(AttributeCollection $attributes, array $parameters, array $params, array $posts)
    {
        $args = array();
        if (!empty($parameters)) {
            /** @var ReflectionParameter $parameter */
            foreach ($parameters as $parameter) {
                $attribute = ParameterAttribute::get($attributes, $parameter->name);
                if (array_key_exists($parameter->name, $posts) && isset($attribute) && $attribute->isPost()) {
                    $arg = new PostArgument($parameter->name, $posts[$parameter->name], $parameter);
                }
                if (array_key_exists($parameter->name, $params) && (!isset($attribute) || $attribute->isGet())) {
                    $arg = new GetArgument($parameter->name, $params[$parameter->name], $parameter);
                }
                if (isset($arg)) {
                    $args[$parameter->name] = $arg->getValue();
                } else {
                    $args[$parameter->name] = null;
                }
            }
        }
        return $args;
    }
}