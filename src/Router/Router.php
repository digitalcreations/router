<?php

namespace DC\Router;

class Router {
    /**
     * @var IRouteMatcher
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
     * @var IClassFactory
     */
    private $classFactory;
    /**
     * @var array|IGlobalFilter[]
     */
    private $filters;

    /**
     * @param IRouteMatcher $routeMatcher
     * @param IRouteFactory $routeFactory
     * @param IResponseWriter $responseWriter
     * @param IClassFactory $classFactory
     * @param \DC\Router\IGlobalFilter[] $filters
     */
    function __construct(IRouteMatcher $routeMatcher,
                         IRouteFactory $routeFactory,
                         IResponseWriter $responseWriter,
                         IClassFactory $classFactory,
                         array $filters = null)
    {
        $this->routeMatcher = $routeMatcher;
        $this->routes = $routeFactory->getRoutes();
        $this->responseWriter = $responseWriter;
        $this->classFactory = $classFactory;
        $this->filters = is_array($filters) ? $filters : [];
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
     * @param $method
     * @param $request
     * @param $route
     * @param $routeOrderedParams
     * @param null $response
     * @return bool
     */
    private function applyFilters($method, $request, $route, $routeOrderedParams, $rawParams, $response = null) {
        foreach ($this->filters as $filter) {
            $filterResponse = call_user_func_array([$filter, $method], [$request, $route, $routeOrderedParams, $rawParams, $response]);
            if ($filterResponse instanceof IResponse) {
                $this->responseWriter->writeResponse($filterResponse);
                return true;
            }
        }
        return false;
    }

    /**
     * Call this function to route the current request and output the result.
     * @param IRequest $request
     * @throws Exceptions\ResponseContentIsNotStringException
     */
    public function route(IRequest $request) {
        $route = $this->routeMatcher->findRoute($request, $this->routes);
        $callable = $route->getCallable();
        if (is_array($callable) && is_string($callable[0]) && class_exists($callable[0])) {
            $instance = $this->classFactory->constructClass($callable[0]);
            if ($instance instanceof IController) {
                $callable[0] = $instance;
                $controller = $instance;
            }
        }

        $result = null;
        $routeOrderedParams = array_merge($this->getDefaultParameterValueMap($callable), $this->routeMatcher->extractParameters($request, $route));
        $rawParams = $this->routeMatcher->extractParameters($request, $route, true);
        try {
            if ($this->applyFilters("beforeRouteExecuting", $request, $route, $routeOrderedParams, $rawParams)) return;

            if (isset($controller) && $controller instanceof IController) {
                $controller->setRequest($request);
                $controller->beforeRoute($routeOrderedParams);
            }
        }
        catch(\Exception $e) {
            $result = $e;
        }

        if (!($result instanceof \Exception)) {
            $order = $this->getCallableParameterOrder($callable);
            $methodOrderedParams = array_map(function($name) use ($routeOrderedParams) {
                return isset($routeOrderedParams[$name]) ? $routeOrderedParams[$name] : null;
            }, $order);
            try {
                ob_start();

                if ($this->applyFilters("routeExecuting", $request, $route, $routeOrderedParams, $rawParams)) return;

                $result = call_user_func_array($callable, $methodOrderedParams);
            } catch (\Exception $e) {
                $result = $e;
            }
            finally {
                $output = ob_get_clean();
            }
        }

        if (!($result instanceof IResponse)) {
            $response = new Response();
            $response->setContentType("text/html");
            $response->setContent(is_null($result) ? $output : $result);
        } else {
            $response = $result;
        }

        if ($this->applyFilters("afterRouteExecuting", $request, $route, $routeOrderedParams, $rawParams, $response)) return;

        if (isset($controller) && $controller instanceof IController) {
            $controller->afterRoute($routeOrderedParams, $response);
        }

        if ($this->applyFilters("afterRouteExecuted", $request, $route, $routeOrderedParams, $rawParams, $response)) return;


        if ($response->getContent() !== null && !is_string($response->getContent())) {
            throw new Exceptions\ResponseContentIsNotStringException($request);
        }

        $this->responseWriter->writeResponse($response);
    }
}