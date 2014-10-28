<?php

namespace DC\Router;

interface IGlobalFilter {
    /**
     * This runs before the controller's beforeRoute.
     *
     * @param IRequest $request The request that is being processed.
     * @param IRoute $route The route that was selected
     * @param $params string[] The params that will be sent to the route function, in order.
     * @param $rawParams string[] The params that were gotten from the URL as strings
     * @return IResponse Return a response if you want to stop running the route immediately.
     */
    function beforeRouteExecuting(IRequest $request, IRoute $route, array $params, array $rawParams);

    /**
     * Pre-route callback. Use if you want to override some results. This runs before the controller's action.
     *
     * @param IRequest $request The request that is being processed.
     * @param IRoute $route The route that was selected.
     * @param $params string[] The params that will be sent to the route function, in order.
     * @param $rawParams string[] The params that were gotten from the URL as strings
     * @return IResponse Return a response if you want to stop running the route immediately.
     */
    function routeExecuting(IRequest $request, IRoute $route, array $params, array $rawParams);

    /**
     * Post-route callback. Use if you want to override some results. This runs before the controller's afterRoute.
     *
     * Modify the response as you see fit.
     *
     * @param IRequest $request The request that is being processed.
     * @param IRoute $route The route that was selected.
     * @param $params string[] The params that will be sent to the route function, in order.
     * @param $rawParams string[] The params that were gotten from the URL as strings
     * @param IResponse $response The response returned by the route, or a response generated from the route.
     * @return mixed Return whatever you want.
     */
    function afterRouteExecuting(IRequest $request, IRoute $route, array $params, array $rawParams, IResponse $response);

    /**
     * Post-route callback. Use if you want to override some results. This runs after the controller's afterRoute.
     *
     * @param IRequest $request The request that is being processed.
     * @param IRoute $route The route that was selected.
     * @param $params string[] The params that will be sent to the route function, in order.
     * @param $rawParams string[] The params that were gotten from the URL as strings
     * @param IResponse $response The response returned by the route, or a response generated from the route.
     * @return mixed Return whatever you want.
     */
    function afterRouteExecuted(IRequest $request, IRoute $route, array $params, array $rawParams, IResponse $response);
}