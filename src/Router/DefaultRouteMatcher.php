<?php
namespace DC\Router;

class DefaultRouteMatcher implements IRouteMatcher {

    const PARAMETER_DELIMITER_START = '{';
    const PARAMETER_DELIMITER_END = '}';

    /**
     * @var IParameterTypeFactory
     */
    private $parameterFactory;

    /**
     * @var \DC\Cache\ICache
     */
    private $cache;

    private $regexCache = array();
    private $regexCacheModified = false;
    /**
     * @var \DC\JSON\Serializer
     */
    private $serializer;

    /**
     * @param $parameterFactory IParameterTypeFactory
     * @param $caches array|\DC\Cache\ICache[]
     * @param \DC\JSON\Serializer $serializer
     */
    public function __construct(IParameterTypeFactory $parameterFactory, $caches = array(), \DC\JSON\Serializer $serializer) {

        $this->parameterFactory = $parameterFactory;
        if (count($caches) > 0) {
            $this->cache = $caches[0];
            $this->regexCache = $this->cache->get('DefaultRouteMatcher::regexCache');
        }
        $this->serializer = $serializer;
    }

    function __destruct()
    {
        if ($this->regexCacheModified && isset($this->cache)) {
            $this->cache->set('DefaultRouteMatcher::regexCache', $this->regexCache);
        }
    }

    /**
     * @param \DC\Router\IRequest|string $request string The HTTP method (GET, POST, PUT, etc)
     * @param IRoute[] $routes The available routes
     * @throws Exceptions\RouteNotFoundException
     * @throws Exceptions\MultipleRoutesFoundException
     * @internal param string $path The requested path ('/user/3/details')
     * @return IRoute
     */
    public function findRoute(IRequest $request, array $routes) {
        $matchingRoutes = array_values(array_filter($routes, function($route) use ($request) {
            return $this->doesMatchRoute($request, $route);
        }));
        if (count($matchingRoutes) == 1) {
            return $matchingRoutes[0];
        }
        if (count($matchingRoutes) == 0) {
            throw new \DC\Router\Exceptions\RouteNotFoundException($request);
        }
        throw new \DC\Router\Exceptions\MultipleRoutesFoundException($request);
    }

    /**
     * Create a regular expression for the path part of a route that will match any valid request for this route.
     *
     * @param IRoute $route
     * @return string
     */
    private function getRouteRegularExpression(IRoute $route) {
        $path = $route->getPath();
        if (isset($this->regexCache[$path])) {
            return $this->regexCache[$path];
        }

        // find variable:type between start and end delimiters, limited to what would be a valid PHP variable name
        $findParamsRegex = '#'.self::PARAMETER_DELIMITER_START.'[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)?'.self::PARAMETER_DELIMITER_END.'#';

        $query = parse_url($path, PHP_URL_QUERY);
        if ($query != null && $query != '') {
            $path = str_replace((string)$query, '', $path);
        }

        $regex = '#^'.preg_replace_callback($findParamsRegex, function($matches) {
                $replacement = trim($matches[0], '{}');
                $parts = explode(':', $replacement);
                $name = $parts[0];
                if (count($parts) > 1) {
                    $type = $parts[1];
                }
                return '(?P<' . $name .'>'.
                (!isset($type) ? '[^/]*' : $this->parameterFactory->getParameterFromType($type)->getRegularExpression()).
                ')';
            }, $path).'$#';

        $this->regexCache[$path] = $regex;
        $this->regexCacheModified = true;

        return $regex;
    }

    /**
     * @param $path
     * @return array[array] Array of matches, second-level array has keys 'name', 'type'
     */
    private function extractParameterInfo($path) {
        // find variable:type between start and end delimiters, limited to what would be a valid PHP variable name
        $findParamsRegex = '#'.self::PARAMETER_DELIMITER_START.'(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*):?(?P<type>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)?'.self::PARAMETER_DELIMITER_END.'#';
        preg_match_all($findParamsRegex, $path, $matches, PREG_SET_ORDER);
        return $matches;
    }

    private function getRouteParameterNameToTypeMap($path) {
        $matches = $this->extractParameterInfo($path);
        $result = array();
        foreach ($matches as $parameter) {
            $result[$parameter['name']] = isset($parameter['type']) ? ltrim($parameter['type'], ':') : 'string';
        }
        return $result;
    }

    private function doesMatchRoute(IRequest $request, IRoute $route) {
        if ($route->getMethod() != null && $request->getMethod() != $route->getMethod()) return false;

        $requestPath = parse_url($request->getPath(), PHP_URL_PATH);
        return (bool)preg_match($this->getRouteRegularExpression($route), $requestPath);
    }

    private function getParameterInfoInternal(IRoute $route) {
        $pathWithoutQuery = parse_url($route->getPath(), PHP_URL_PATH);

        $parameterHashmap = $this->getRouteParameterNameToTypeMap($pathWithoutQuery);
        $parameters = [];
        foreach ($parameterHashmap as $name => $type) {
            $parameters[] = ['\DC\Router\Parameters\PathRouteParameter', $name, $type];
        }

        $query = parse_url($route->getPath(), PHP_URL_QUERY);
        if ($query != null) {
            $queryParameters = [];
            parse_str($query, $queryParameters);
            foreach ($queryParameters as $queryName => $variable) {
                $parsed = $this->extractParameterInfo($variable);
                if (!isset($parsed[0])) continue;
                $parameters[] = ['\DC\Router\Parameters\QueryRouteParameter', $parsed[0]['name'], $queryName, $parsed[0]['type']];
            }
        }

        // create the body parameter
        $callable = $route->getCallable();
        $reflector = new \DC\Router\Swagger\Reflector();
        $reflection = $reflector->getReflectionFunctionForCallable($callable);
        $reflectionParameters = $reflection->getParameters();
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);

        $bodyParameterName = "body";
        $bodyTags = $phpdoc->getTagsByName(\DC\Router\BodyTag::$name);
        $bodyTag = reset($bodyTags);
        if ($bodyTag != null) {
            $bodyParameterName = $bodyTag->getVariableName();
        }

        $bodyParameters = array_filter($reflectionParameters, function (\ReflectionParameter $reflection) use ($bodyParameterName) {
            return $reflection->getName() == $bodyParameterName;
        });
        if (count($bodyParameters) > 0) {
            // check for type hints first
            $className = '\stdClass';
            /** @var \ReflectionParameter $parameter */
            $parameter = reset($bodyParameters);
            if ($parameter->getClass() != null) {
                $className = $parameter->getClass()->getName();
            } else {
                // no type hints, check documentation
                $paramTags = $phpdoc->getTagsByName("param");
                $paramTags = array_filter($paramTags, function (\phpDocumentor\Reflection\DocBlock\Tag\ParamTag $tag) use ($bodyParameterName) {
                    return $tag->getName() == '$' . $bodyParameterName;
                });
                $paramTag = reset($paramTags);
                if ($paramTag != null) {
                    /** @var \phpDocumentor\Reflection\DocBlock\Tag\ParamTag $paramTag */
                    $className = $paramTag->getType();
                }
            }
            $parameters[] = ['\DC\Router\Parameters\BodyRouteParameter', $bodyParameterName, $className];
        }
        return $parameters;
    }

    /**
     * @param IRoute $route
     * @return \DC\Router\Parameters\RouteParameterBase[]
     */
    function getParameterInfo(IRoute $route)
    {
        if ($this->cache != null) {
            $cacheKey = "route_" . $route->getMethod() . "_" . $route->getPath();
            $parameters = $this->cache->getWithFallback($cacheKey, function() use ($route) { return $this->getParameterInfoInternal($route); });
        }
        else {
            $parameters = $this->getParameterInfoInternal($route);
        }

        foreach ($parameters as $key => $parameter) {
            $class = $parameter[0];
            $parameter[0] = $route;
            if ($class == '\DC\Router\Parameters\BodyRouteParameter') {
                $parameter[] = $this->serializer;
            }
            else {
                $parameter[count($parameter) - 1] = $this->parameterFactory->getParameterFromType($parameter[count($parameter) - 1]);
            }
            $parameters[$key] = new $class(...$parameter);
        }

        return $parameters;
    }

    /**
     * Find the values of the parameters for a given route
     *
     * @param \DC\Router\IRequest $request The requested method
     * @param IRoute $route The route that was matched
     * @param bool $rawValues Set to true if you want the raw values
     * @return array Array with parameter name as the key, and the parameter's final value as the values
     */
    function extractParameters(IRequest $request, IRoute $route, $rawValues = false)
    {
        $path = parse_url($request->getPath(), PHP_URL_PATH);
        preg_match_all($this->getRouteRegularExpression($route), $path, $matches, PREG_SET_ORDER);
        $valueMap = array();
        if (count($matches) > 0) {
            // $matches now contain numeric indices of the groups as well as named indices
            // we need to get rid of the numeric indices
            $allowedMatches = array_values(array_filter(array_keys($matches[0]), function($key) {
                return !is_numeric($key);
            }));
            $valueMap = array_intersect_key($matches[0], array_flip($allowedMatches));
        }

        if ($rawValues) {
            return $valueMap;
        }

        $parameters = $this->getParameterInfo($route);

        foreach ($parameters as $parameter) {
            $valueMap[$parameter->getInternalName()] = $parameter->getValueForRequest($request, $valueMap);
        }

        return $valueMap;
    }
}