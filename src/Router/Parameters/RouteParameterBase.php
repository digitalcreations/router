<?php

namespace DC\Router\Parameters;

/**
 * Represents known information about the parameters to a route.
 *
 * @package DC\Router
 */
abstract class RouteParameterBase {

    private $queryName;
    private $internalName;
    /**
     * @var \DC\Router\IParameterType
     */
    private $parameterType;
    /**
     * @var \DC\Router\IRoute
     */
    protected $route;

    /**
     * @param \DC\Router\IRoute $route The route instance.
     * @param string $internalName The name available to the route.
     * @param string $queryName The name available in the query string (to consumers)
     * @param \DC\Router\IParameterType $parameterType
     */
    function __construct(\DC\Router\IRoute $route, $internalName, $queryName, \DC\Router\IParameterType $parameterType = null)
    {
        $this->queryName = $queryName;
        $this->internalName = $internalName;
        $this->parameterType = $parameterType;
        $this->route = $route;
    }

    /**
     * @return string
     */
    function getQueryName() {
        return $this->queryName;
    }

    /**
     * @return string
     */
    function getInternalName() {
        return $this->internalName;
    }

    /**
     * @return \DC\Router\IParameterType
     */
    function getParameterType() {
        return $this->parameterType;
    }

    /**
     * Return the value of a parameter for a given request.
     *
     * @param \DC\Router\IRequest $request
     * @param array $valueMap
     * @return mixed
     */
    abstract function getValueForRequest(\DC\Router\IRequest $request, array $valueMap);

    /**
     * Get where in the request you can expect to find this parameter.
     *
     * @return string
     * @see \DC\Router\ParameterPlacement
     */
    abstract function getPlacement();
}