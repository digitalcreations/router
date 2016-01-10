<?php

namespace DC\Router;

interface IRouteMatcher {
    /**
     * @param IRequest $request
     * @param \DC\Router\IRoute[] $routes The available routes
     * @internal param string $method The HTTP method (GET, POST, PUT, etc)
     * @internal param string $path The requested path ('/user/3/details')
     * @return IRoute
     */
    function findRoute(IRequest $request, array $routes);

    /**
     * Find the values of the parameters for a given route
     *
     * @param IRequest $request
     * @param IRoute $route The route that was matched
     * @param bool $rawValues Set to true if you want the raw values
     * @return array Array with parameter name as the key, and the parameter's final value as the values
     */
    function extractParameters(IRequest $request, IRoute $route, $rawValues = false);

    /**
     * @param IRoute $route
     * @return \DC\Router\Parameters\RouteParameterBase[]
     */
    function getParameterInfo(IRoute $route);
}