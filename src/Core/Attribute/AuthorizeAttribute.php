<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/7
 * Time: 23:10
 */

namespace Xaircraft\Core\Attribute;


use Xaircraft\Authentication\Contract\Authorize;
use Xaircraft\DI;
use Xaircraft\Exception\AttributeException;
use Xaircraft\Exception\HttpAuthenticationException;
use Xaircraft\Web\Mvc\HttpAuthCredential;

class AuthorizeAttribute extends Attribute
{
    private $authorize;

    private $arguments = array();

    public function initialize($value)
    {
        if (preg_match('#(?<authorize>[a-zA-Z][a-zA-Z0-9\_\\\]+)(\((?<arguments>[^\)]+)\))?#i', $value, $matches)) {
            $this->authorize = $matches['authorize'];
            if (array_key_exists('arguments', $matches)) {
                $this->arguments = $this->parseArguments($matches['arguments']);
            }
        }
    }

    private function parseArguments($source)
    {
        $arguments = array();
        $items = explode(',', $source);
        if (!empty($items)) {
            foreach ($items as $item) {
                if (preg_match('#(?<key>[a-zA-Z][a-zA-Z0-9\_]+)\=[\'\"]?(?<value>[^\'\"\,]+)[\'\"]?#i', $item, $matches)) {
                    $arguments[$matches['key']] = trim($matches['value']);
                } else {
                    throw new AttributeException("Invalid attribute arguments.");
                }
            }
        }
        return $arguments;
    }

    /**
     * @throws HttpAuthenticationException
     */
    public function invoke()
    {
        $authorize = DI::get($this->authorize, $this->arguments);
        if (isset($authorize) && $authorize instanceof Authorize) {
            try {
                if (!$authorize->authorize(DI::get(HttpAuthCredential::class))) {
                    throw new HttpAuthenticationException("Http authorize failure [$this->authorize].");
                }
            } catch (\Exception $ex) {
                throw new HttpAuthenticationException($ex->getMessage(), $ex);
            }
        }
    }
}