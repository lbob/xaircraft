<?php namespace Xaircraft\Router;

/**
 * Class Routes
 *
 * @author lbob created at 2014/11/27 10:11
 */
class Router
{

    const PATTERN_BASE = '[a-z][a-z0-9\_\-]*';
    const PATTERN_TOKEN = '#\{([^\{\}]+)\}#i';
    const PATTERN_ID = '[\d]+';
    const PATTERN_NAME = '[a-z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
    const PATTERN_TAIL = '.+';
    const PATTERN_NAMESPACE = '#^\/?([^\{\}]+[a-zA-Z0-9])#i';

    const PATTERN_TOKEN_LESS = '#\/(\{[^\{\}]+\}\?)#i';
    const PATTERN_TOKEN_NOT_LESS = '#\/(\{[^\{\}]+\})#i';
    const PATTERN_TOKEN_COMPLETE = '#\{([^\{\}]+)\}\??#i';

    /**
     * @var array 映射表，用于对路由进行配置
     *
     * 包括以下字段：expression, pattern, handler, default, filter
     */
    public $mappings = array();
    public $baseMappings
        = array(
            'default' => array(
                'expression' => '/{controller}?/{action}?/{id}?',
                'default'    => array(
                    'controller' => 'home',
                    'action'     => 'index',
                    'id'         => 0
                )
            )
        );

    /**
     * @var array Nebula\Route 编译 mappings 之后得到的路由表
     *
     * 包括以下字段：
     * expression: 原表达式解释成用于匹配URL的正则表达式, patterns, tokens: token集合, beforeHandlers,
     * matchedHandlers, afterHandlers,defaultValues, beforeFilters, afterFilters
     */
    private $routes = array();
    private $defaultTokenPatterns
        = array(
            'controller' => self::PATTERN_BASE,
            'action'     => self::PATTERN_BASE,
            'id'         => self::PATTERN_ID,
            'name'       => self::PATTERN_NAME,
            'tail'       => self::PATTERN_TAIL,
        );
    /**
     * @var \Xaircraft\Router\RouteResult
     */
    public $routeResult;
    /**
     * @var array Xaircraft\Router\Filter
     */
    private $filters = array();
    private $isAbort = false;
    /**
     * @var string
     */
    private $filterDir;
    private static $instance;
    private $timestamp = -1;
    private $source;
    /**
     * @var \Xaircraft\Router\RouteCache
     */
    private $routeCache;
    private $filterBinders = array();
    private $missingHandler;
    private $defaultMatchedHandlers = array();
    private $matchedHandlers = array();

    public function __construct()
    {
        $this->routeCache = new RouteCache();
    }

    public static function getInstance($configDir = null, $filterDir = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = self::make($configDir, $filterDir);
        }
        return self::$instance;
    }

    public function missing($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->missingHandler = $handler;
        }
    }

    public function compileRoutes()
    {
        unset($this->routes);
        $this->routes = array();
        $this->routeCache->readCache();
        foreach ($this->mappings as $mappingName => $mapping) {
            $route              = new Route();
            $route->mappingName = $mappingName;

            //expression, patterns, tokens 能缓存的也就是这些数据了
            list($expression, $patterns, $tokens, $namespace) = $this->getBaseRouteData($mappingName, $mapping);
            $route->namespace  = $namespace;
            $route->expression = $expression;
            $route->patterns   = $patterns;
            $route->tokens     = $tokens;

            //handler
            if (array_key_exists('handler', $mapping)) {
                foreach ($mapping['handler'] as $handlerName => $handler) {
                    if ($handlerName === 'before') $beforeHandlers[] = $handler;
                    if ($handlerName === 'matched') $matchedHandlers[] = $handler;
                    if ($handlerName === 'after') $afterHandlers[] = $handler;
                }
                $route->beforeHandlers  = isset($beforeHandlers) ? $beforeHandlers : null;
                $route->matchedHandlers = isset($matchedHandlers) ? $matchedHandlers : null;
                $route->afterHandlers   = isset($afterHandlers) ? $afterHandlers : null;
            }

            //defaultValues
            if (array_key_exists('default', $mapping)) {
                $route->defaultValues = $mapping['default'];
            } else {
                //为每个匹配的路由添加默认的default配置
                $route->defaultValues = array(
                    'controller' => 'home',
                    'action' => 'index'
                );
            }

            //beforeFilters, afterFilters
            if (array_key_exists('filter', $mapping)) {
                foreach ($mapping['filter'] as $filterName => $filters) {
                    if ($filterName === 'before') $beforeFilters[] = $filters;
                    if ($filterName === 'after') $afterFilters[] = $filters;
                }
                $route->beforeFilters = isset($beforeFilters) ? $beforeFilters : null;
                $route->afterFilters  = isset($afterFilters) ? $afterFilters : null;
            }

            $this->routes[$mappingName] = $route;
        }
        $this->routeCache->writeCache();
    }

    public function registerIsCacheExpiredHandler($handler)
    {
        $this->routeCache->registerIsExpiredHandler($handler);
    }

    public function registerReadCacheHandler($handler)
    {
        $this->routeCache->registerReadCacheHandler($handler);
    }

    public function registerWriteCacheHandler($handler)
    {
        $this->routeCache->registerWriteCacheHandler($handler);
    }

    public function registerDefaultMatchedHandler($handler)
    {
        if (isset($handler) && is_callable($handler))
            $this->defaultMatchedHandlers[] = $handler;
    }

    public function registerMatchedHandler($handler)
    {
        if (isset($handler) && is_callable($handler))
            $this->matchedHandlers[] = $handler;
    }

    public function routing()
    {
        if (func_num_args() > 0)
            $url = func_get_arg(0);
        else
            $url = $_SERVER['REQUEST_URI'];

        $this->compileRoutes();
        $this->routeResult = $this->match($url);
        if ($this->isMatched() === true) {
            list($beforeFilters, $afterFilters) = $this->matchFilter();
            /**
             * @var $route \Xaircraft\Router\Route
             */
            $route = $this->routes[$this->routeResult->mappingName];
            array_push($route->beforeFilters, $beforeFilters);
            array_push($route->afterFilters, $afterFilters);

            //matchedHandlers
            if (isset($this->matchedHandlers) && !empty($this->matchedHandlers))
                $this->invokeHandlers($this->matchedHandlers);
            if ($this->isAbort()) return;

            //beforeFilters
            $this->invokeFilters($route->beforeFilters);
            if ($this->isAbort()) return;

            //beforeHandlers
            $this->invokeHandlers($route->beforeHandlers);
            if ($this->isAbort()) return;

            //matchedHandlers
            if (isset($route->matchedHandlers) && !empty($route->matchedHandlers))
                $this->invokeHandlers($route->matchedHandlers);
            else if (isset($this->defaultMatchedHandlers) && !empty($this->defaultMatchedHandlers))
                $this->invokeHandlers($this->defaultMatchedHandlers);
            if ($this->isAbort()) return;

            //afterFilters
            $this->invokeFilters($route->afterFilters);
            if ($this->isAbort()) return;

            //afterHandlers
            $this->invokeHandlers($route->afterHandlers);
            if ($this->isAbort()) return;
        } else {
            $this->onMissing();
        }
    }

    public function isMatched()
    {
        if (isset($this->routeResult))
            return $this->routeResult->isMatched;
        return false;
    }

    public function registerFilter($name, $handler)
    {
        if (isset($name) && isset($handler)) {
            $this->filters[$name] = new Filter($name, $this->getFilterPath($name), $handler);
        }
    }

    public function bindFilter()
    {
        $this->filterBinders[] = new FilterBinder(func_get_args());
    }

    public function reverse($url, $params)
    {
        if (!isset($params))
            $params = array();
        $routeResult = $this->match($url);
        if (isset($routeResult) && $routeResult->isMatched) {
            foreach ($routeResult->params as $key => $value) {
                if (!array_key_exists($key, $params))
                    $params[$key] = $value;
            }
            return $this->reverseByRoute($routeResult->mappingName, $params);
        }
        return '/'; //无匹配的则返回到根目录
    }

    public function reverseByRoute($routeName, $params)
    {
        /**
         * @var $route \Xaircraft\Router\Route
         */
        $route      = $this->routes[$routeName];
        $expression = $this->mappings[$routeName]['expression'];
        $replaced   = array();
        if (preg_match_all(self::PATTERN_TOKEN_COMPLETE, $expression, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $item) {
                $matchedString = $item[0];
                $matchedToken  = $item[1];
                if (array_key_exists($matchedToken, $params))
                    $expression = str_replace($matchedString, $params[$matchedToken], $expression);
                else {
                    if (strpos($matchedString, '?') > 0) {
                        $expression = str_replace($matchedString, '', $expression);
                    } else {
                        throw new \InvalidArgumentException("Reverse fail: Can't find [$matchedToken] in [$expression] (Matched route name is [$route->mappingName])");
                    }
                }
                $replaced[$matchedToken] = $matchedToken;
            }
            $expression = str_replace('//', '/', $expression);
        }
        if (array_key_exists('namespace', $params))
            unset($params['namespace']);
        $tails = array();
        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $replaced)) {
                $tails[] = $key . '=' . $value;
            }
        }
        if (isset($tails) && !empty($tails))
            $expression = $expression . '?' . implode('&', $tails);
        return $expression;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    private function loadBaseMappings()
    {
        if (isset($this->baseMappings) && !empty($this->baseMappings)) {
            foreach ($this->baseMappings as $key => $value) {
                if (!array_key_exists($key, $this->mappings)) {
                    $this->mappings[$key] = $value;
                }
            }
        }
    }

    private function onMissing()
    {
        if (isset($this->missingHandler) && is_callable($this->missingHandler))
            call_user_func($this->missingHandler);
    }

    private static function make($configDir, $filterDir)
    {
        if (!isset($router)) {
            $router = new Router();
        }
        if (isset($configDir)) {
            if (is_file($configDir) && is_readable($configDir)) {
                $router->timestamp = filemtime($configDir);
                $router->source    = $configDir;
                require $configDir;
            }
        }
        if (isset($filterDir)) {
            $router->filterDir = $filterDir;
        }
        $router->loadBaseMappings();
        return $router;
    }

    private function getBaseRouteData($mappingName, $mapping)
    {
        if (!$this->routeCache->isCacheExpired($this->timestamp)) {
            $data = $this->routeCache->getData($mappingName);
            if (isset($data)) return $data;
        }

        if (array_key_exists('expression', $mapping)) {
            $expression = $mapping['expression'];
            $tokens     = $this->getTokens($expression);
            $patterns   = $this->getTokenPatterns($mappingName, $tokens);

            //namespace
            $namespace = null;
            if (preg_match(self::PATTERN_NAMESPACE, $expression, $matches)) {
                if (!empty($matches[1])) $namespace = $matches[1];
            }

            $result = array(
                $this->parseExpression($expression, $tokens, $patterns, $namespace),
                $patterns,
                $tokens,
                $namespace
            );
            $this->routeCache->setData($mappingName, $result);
            return $result;
        }
        return null;
    }

    private function matchFilter()
    {
        $handlers = array();
        if (isset($this->routeResult) && $this->isMatched()) {
            //构造匹配
            $expression = '';
            foreach ($this->routeResult->params as $key => $value) {
                $expression = $expression . '[' . $key . '=' . $value . ']';
            }
            /**
             * @var $filterBinder \Xaircraft\FilterBinder
             */
            foreach ($this->filterBinders as $filterBinder) {
                //优先匹配
                if (isset($filterBinder->expression)) {
                    if (preg_match($filterBinder->expression, $expression, $matches)) {
                        //匹配成功
                        $handlers = $filterBinder->handlers;
                        break;
                    }
                }
                if ($this->routeResult->mappingName === $filterBinder->mappingName) {
                    $handlers = $filterBinder->handlers;
                }
            }
        }
        $beforeFilters = array();
        $afterFilters = array();
        foreach ($handlers as $key => $value) {
            if ($key === 'before')
                $beforeFilters = $value;
            if ($key === 'after')
                $afterFilters = $value;
        }
        return array($beforeFilters, $afterFilters);
    }

    private function getFilterPath($filter)
    {
        return $this->filterDir . DIRECTORY_SEPARATOR . $filter . '.php';
    }

    private function invokeHandlers($handlers)
    {
        if (isset($handlers)) {
            foreach ($handlers as $handler) {
                if (is_callable($handler)) {
                    if ($handler($this->routeResult->params) === false) {
                        $this->abort();
                        return;
                    }
                }
            }
        }
    }

    private function invokeFilters($filters)
    {
        if (isset($filters)) {
            foreach ($filters as $filter) {
                $handlers = array();
                if (is_callable($filter)) {
                    $handlers[] = $filter;
                }
                if (is_array($filter)) {
                    foreach ($filter as $filterName) {
                        if (is_string($filterName)) {
                            if (!array_key_exists($filterName, $this->filters)) {
                                $path = $this->getFilterPath($filterName);
                                if (is_file($path) && is_readable($path)) {
                                    $router = $this;
                                    require $path;
                                }
                            }
                            if (!array_key_exists($filterName, $this->filters)) {
                                throw new \InvalidArgumentException("Can't find filter [$filterName] in route [" . $this->routeResult->mappingName . "]");
                            } else {
                                $handlers = array($this->filters[$filterName]->handlers);
                            }
                        }
                    }
                }
                $this->invokeHandlers($handlers);
                if ($this->isAbort()) return;
            }
        }
    }

    public function match($url)
    {
        if (isset($url)) {
            $params = array();
            /**
             * @var $route Route
             */
            foreach ($this->routes as $route) {
                if (preg_match_all($route->expression, $url, $matches, PREG_SET_ORDER)) {
                    if (isset($route->namespace))
                        $params['namespace'] = $route->namespace;
                    $match = $matches[0];
                    foreach ($route->tokens as $token) {
                        if (!empty($match[$token]))
                            $params[$token] = $match[$token];
                        else
                            $params[$token] = null;
                    }
                    if (!empty($match['tail'])) {
                        parse_str($match['tail'], $others);
                        if (isset($others)) {
                            foreach ($others as $key => $value) {
                                $params[$key] = $value;
                            }
                        }
                    }
                    return new RouteResult($url, $route->mappingName, true, $params, $route->defaultValues);
                }
            }
        }
        return null;
    }

    private function parseExpression($expression, $tokens, $patterns, $namespace)
    {
        $expression = preg_replace('#(/+)$#i', '', $expression);
        $expression = preg_replace(self::PATTERN_TOKEN_LESS, '\/?$1', $expression);
        $expression = preg_replace(self::PATTERN_TOKEN_NOT_LESS, '\/$1', $expression);
        foreach ($tokens as $token) {
            $expression = str_replace('{' . $token . '}', '(?<' . $token . '>' . $patterns[$token] . ')', $expression);
        }
        if (isset($namespace)) {
            $namespacePattern = '#^/' . $namespace . '#i';
            $replaceNamespace = '\/?' . $namespace;
            $expression = preg_replace($namespacePattern, $replaceNamespace, $expression);
        }
        return '#^' . $expression . '\/?(?:\?(?<tail>' . self::PATTERN_TAIL . '))?$#i';
    }

    private function getTokens($expression)
    {
        $tokens = array();
        if (preg_match_all(self::PATTERN_TOKEN, $expression, $tokenMatches, PREG_SET_ORDER)) {
            foreach ($tokenMatches as $tokenMatch) {
                $tokens[] = $tokenMatch[1];
            }
        }
        return $tokens;
    }

    private function getTokenPatterns($mappingName, $tokens)
    {
        $allPatterns = array();
        if (array_key_exists('pattern', $this->mappings[$mappingName])) {
            $allPatterns = $this->mappings[$mappingName]['pattern'];
            foreach ($this->defaultTokenPatterns as $key => $value) {
                if (!array_key_exists($key, $allPatterns))
                    $allPatterns[$key] = $value;
            }
        } else {
            $allPatterns = $this->defaultTokenPatterns;
        }

        $patterns = array();
        foreach ($tokens as $token) {
            if (array_key_exists($token, $allPatterns)) {
                $patterns[$token] = $allPatterns[$token];
            } else {
                throw new \InvalidArgumentException("Missing token pattern [$token] in mapping [$mappingName].");
            }
        }
        return $patterns;
    }

    private function abort()
    {
        $this->isAbort = true;
    }

    private function isAbort()
    {
        return $this->isAbort;
    }
}
 