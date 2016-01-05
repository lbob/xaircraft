<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 14:26
 */

namespace Xaircraft\Web\Mvc;


use Xaircraft\Core\Attribute\AttributeCollection;
use Xaircraft\Core\Attribute\OutputStatusExceptionAttribute;
use Xaircraft\DI;
use Xaircraft\Exception\HttpAuthenticationException;
use Xaircraft\Web\Mvc\Argument\Argument;

class ActionInfo
{
    /**
     * @var Controller
     */
    private $controller;

    private $name;

    private $parameters;

    private $docComment;

    /**
     * @var \ReflectionMethod
     */
    private $reflector;

    /**
     * @var AttributeCollection
     */
    private $attributes;

    public function __construct(Controller $controller, $action)
    {
        $this->controller = $controller;
        $this->name = $action;
        $this->reflector = new \ReflectionMethod($controller, $action);
        $this->docComment = $this->reflector->getDocComment();
        $this->parameters = $this->reflector->getParameters();

        $this->initializeAttributes();
    }

    public function invoke(array $params = null, array $posts = null)
    {
        $this->attributes->invoke();
        $this->controller->attributes->invoke();
        $args = Argument::createArgs($this->attributes, $this->parameters, $params, $posts);
//        foreach ($this->parameters as $parameter) {
//            var_dump($parameter->getClass());
//            if (array_key_exists($parameter->name, $params)) {
//                $args[$parameter->name] = $params[$parameter->name];
//            } else {
//                $args[$parameter->name] = null;
//            }
//        }
        return $this->reflector->invokeArgs($this->controller, $args);
    }

    public function getIfOutputStatusException()
    {
        return $this->attributes->exists(OutputStatusExceptionAttribute::class);
    }

    private function initializeAttributes()
    {
        $this->attributes = AttributeCollection::create($this->docComment);
    }
}