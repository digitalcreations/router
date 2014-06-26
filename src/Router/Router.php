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
     * @param IRouteMatcher $routeMatcher
     * @param IRouteFactory $routeFactory
     * @param IResponseWriter $responseWriter
     * @param IClassFactory $classFactory
     */
    function __construct(IRouteMatcher $routeMatcher,
                         IRouteFactory $routeFactory,
                         IResponseWriter $responseWriter,
                         IClassFactory $classFactory)
    {
        $this->routeMatcher = $routeMatcher;
        $this->routes = $routeFactory->getRoutes();
        $this->responseWriter = $responseWriter;
        $this->classFactory = $classFactory;
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
        if (is_array($callable) && is_string($callable[0]) && class_exists($callable[0])) {
            $instance = $this->classFactory->constructClass($callable[0]);
            if ($instance instanceof IController) {
                $callable[0] = $instance;
                $controller = $instance;
            }
        }

        $result = null;
        $routeOrderedParams = array_merge($this->getDefaultParameterValueMap($callable), $this->routeMatcher->extractParameters($request, $route));
        try {
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
                return $routeOrderedParams[$name];
            }, $order);
            try {
                ob_start();
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

        if (isset($controller) && $controller instanceof IController) {
            $controller->afterRoute($routeOrderedParams, $response);
        }

        if ($response->getContent() !== null && !is_string($response->getContent())) {
            throw new Exceptions\ResponseContentIsNotStringException($request);
        }

        $this->responseWriter->writeResponse($response);
    }
}