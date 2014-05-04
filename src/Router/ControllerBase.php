<?php

namespace DC\Router;

/**
 * Group a bunch of routes together in one class.
 *
 * Decorate any method you want to do routing with a PHPdoc comment like:
 *   @route GET /foo/{id:int}
 *
 * @codeCoverageIgnore
 */
abstract class ControllerBase implements IController {

    /**
     * @var IRequest
     */
    private $request;

    /**
     * Pre-route callback. Mostly used for authorization.
     *
     * @param $params string[] The arguments the route function will be invoked with, keys are parameter names.
     * @return bool Return false if you want to stop the route from executing (403 Forbidden)
     */
    function beforeRoute(array $params)
    {
        return true;
    }

    /**
     * Post-route callback. Use if you want to change responses.
     *
     * @param $params string[] The arguments the route function was invoked with, keys are parameter names.
     * @param IResponse $response Modify the response as you see fit.
     * @return void
     * @internal param string $result The result from the route function.
     */
    function afterRoute(array $params, IResponse $response)
    {

    }

    function setRequest(IRequest $request) {
        $this->request = $request;
    }

    /**
     * @return IRequest The currently processing request.
     */
    function getRequest() {
        return $this->request;
    }
}