<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/15
 * Time: 14:25
 */

namespace Xaircraft\Web\Mvc\Argument;


use ReflectionParameter;
use Xaircraft\Core\Attribute\Attribute;
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

    protected $attribute;

    public function __construct($name, $value, ReflectionParameter $reflectionParameter, ParameterAttribute $attribute = null)
    {
        $this->value = $value;
        $this->name = $name;
        $this->reflectionParameter = $reflectionParameter;
        $this->attribute = $attribute;

        $this->initialize();
    }

    private function initialize()
    {
        if ($this->reflectionParameter->isArray()) {
            $this->value = Json::toArray(
                $this->value,
                isset($this->attribute) && $this->attribute->isArray() ? $this->attribute->getType() : null
            );
        }
        if (!isset($this->value)) {
            if ($this->reflectionParameter->isOptional()) {
                $defaultValue = $this->reflectionParameter->getDefaultValue();
                $this->value = $defaultValue;
            }
        }
        $class = $this->reflectionParameter->getClass();
        if (isset($class)) {
            if ($class->getName() === PostFile::class) {
                $this->value = new PostFile($this->value);
            } else {
                $this->value = Json::toObject($this->value, $class);
            }
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

    public static function createArgs(AttributeCollection $attributes, array $parameters, array $params, array $posts, array $files = null)
    {
        $args = array();
        if (!empty($parameters)) {
            /** @var ReflectionParameter $parameter */
            foreach ($parameters as $parameter) {
                $arg = null;
                $attribute = ParameterAttribute::get($attributes, $parameter->name);
                if (array_key_exists($parameter->name, $posts) && isset($attribute) && $attribute->isPost()) {
                    $arg = new PostArgument($parameter->name, $posts[$parameter->name], $parameter, $attribute);
                }
                if (array_key_exists($parameter->name, $params) && (!isset($attribute) || $attribute->isGet())) {
                    $arg = new GetArgument($parameter->name, $params[$parameter->name], $parameter, $attribute);
                }
                if (!isset($arg) && !empty($files)) {
                    $arg = new FileArgument($parameter->name, $files, $parameter, $attribute);
                }
                if (isset($arg)) {
                    $args[$parameter->name] = $arg->getValue();
                } else {
                    if (!$parameter->allowsNull() && !$parameter->isDefaultValueAvailable()) {
                        throw new ArgumentInvalidException($parameter->name, "Argument [$parameter->name] can't be null.");
                    }
                    if ($parameter->isDefaultValueAvailable()) {
                        $args[$parameter->name] = $parameter->getDefaultValue();
                    } else {
                        $args[$parameter->name] = null;
                    }
                }
            }
        }
        return $args;
    }
}