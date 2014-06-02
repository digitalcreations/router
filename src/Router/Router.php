<?php

namespace DC\Router;

class Router {
    /**
     * @var \DC\Router\IRouteMatcher
     */
    private $routeMatcher;

    /**
     * @var \DC\Router\IRoute[]
     */
    private $routes;
    /**
     * @var IResponseWriter
     */
    private $responseWriter;

    /**
     * @param \DC\Router\IRouteMatcher $routeMatcher
     * @param \DC\Router\IRouteFactory $routeFactory
     * @param \Dc\Router\IResponseWriter $responseWriter
     */
    function __construct(\DC\Router\IRouteMatcher $routeMatcher, \DC\Router\IRouteFactory $routeFactory, \DC\Router\IResponseWriter $responseWriter)
    {
        $this->routeMatcher = $routeMatcher;
        $this->routes = $routeFactory->getRoutes();
        $this->responseWriter = $responseWriter;
    }

    private function getReflectionFunctionForCallable($callable) {
        if ($callable instanceof \Closure || is_string($callable)) {
            return new \ReflectionFunction($callable);
        }
        else if (is_array($callable)) {
            if (is_string($callable[0])) {
                return new \ReflectionMethod($callable[0], $callable[1]);
            } else {
                $reflectionObject = new \ReflectionObject($callable[0]);
                return $reflectionObject->getMethod($callable[1]);
            }
        }
        else {
            throw new \ReflectionException("Could not find parameter order for callable");
        }
    }

    private function getCallableParameterOrder($callable) {
        $reflection = $this->getReflectionFunctionForCallable($callable);
        return array_map(function($parameter) {
            return $parameter->getName();
        }, $reflection->getParameters());
    }

    private function getDefaultParameterValueMap($callable) {
        $reflection = $this->getReflectionFunctionForCallable($callable);
        $parameters = $reflection->getParameters();
        $result = array();
        foreach ($parameters as $parameter) {
            if (!$parameter->isOptional()) continue;
            $result[$parameter->getName()] = $parameter->getDefaultValue();
        }
        return $result;
    }

    /**
     * Call this function to route the current request and output the result.
     */
    public function route(IRequest $request) {
        $route = $this->routeMatcher->findRoute($request, $this->routes);
        $callable = $route->getCallable();
        $routeOrderedParams = array_merge($this->routeMatcher->extractParameters($request, $route), $this->getDefaultParameterValueMap($callable));
        $controller = $route->getController();
        if ($controller instanceof IController) {
            $controller->setRequest($request);
            $controller->beforeRoute($routeOrderedParams);
        }
        $order = $this->getCallableParameterOrder($callable);
        $methodOrderedParams = array_map(function($name) use ($routeOrderedParams) {
            return $routeOrderedParams[$name];
        }, $order);
        ob_start();
        $result = call_user_func_array($callable, $methodOrderedParams);
        $output = ob_get_clean();

        if (!($result instanceof IResponse)) {
            $response = new Response();
            $response->setContentType("text/html");
            $response->setContent(is_null($result) ? $output : $result);
        } else {
            $response = $result;
        }

        if ($controller instanceof IController) {
            $controller->afterRoute($routeOrderedParams, $response);
        }

        if ($response->getContent() !== null && !is_string($response->getContent())) {
            throw new Exceptions\ResponseContentIsNotStringException();
        }

        $this->responseWriter->writeResponse($response);
    }
}