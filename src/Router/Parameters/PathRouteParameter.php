<?php

namespace DC\Router\Parameters;

class PathRouteParameter extends RouteParameterBase {
    function __construct(\DC\Router\IRoute $route, $name, \DC\Router\IParameterType $parameterType = null)
    {
        parent::__construct($route, $name, $name, $parameterType);
    }

    /**
     * Return the value of a parameter for a given request.
     *
     * @param \DC\Router\IRequest $request
     * @param array $valueMap
     * @return mixed
     */
    function getValueForRequest(\DC\Router\IRequest $request, array $valueMap)
    {
        $value = $valueMap[$this->getInternalName()];
        $parameterType = $this->getParameterType();
        if ($parameterType instanceof \DC\Router\IParameterType) {
            $value = $parameterType->transformValue($value);
        }
        return $value;
    }

    /**
     * Get where in the request you can expect to find this parameter.
     *
     * @return \DC\Router\ParameterPlacement
     */
    function getPlacement()
    {
        return \DC\Router\ParameterPlacement::Path;
    }
}