<?php

namespace DC\Router;

/**
 * Represents a grouping of routes.
 *
 * @package DC\Router
 */
interface IController {

    /**
     * Pre-route callback. Mostly used for authorization.
     *
     * @param $params string[] The params that will be sent to the route function, in order.
     * @return bool Return false if you want to stop the route from executing (403 Forbidden)
     */
    function beforeRoute(array $params);

    /**
     * Post-route callback. Use if you want to override some results
     *
     * @param $params string[] The params that will be sent to the route function, in order.
     * @param IResponse $response The response returned by the route, or a response generated from the route.
     * @return mixed Return whatever you want.
     */
    function afterRoute(array $params, IResponse $response);

    function setRequest(IRequest $request);

    /**
     * @return \DC\Router\IRequest
     */
    function getRequest();
}