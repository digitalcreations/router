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
     * @internal param \DC\Router\The $method requested method
     * @internal param \DC\Router\The $path requested path
     * @return array Array with parameter name as the key, and the parameter's final value as the values
     */
    function extractParameters(IRequest $request, IRoute $route);
}