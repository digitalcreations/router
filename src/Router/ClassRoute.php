<?php

namespace DC\Router;

class ClassRoute implements IRoute {
    /**
     * @var string
     */
    private $class;
    /**
     * @var string Name of function to call.
     */
    private $function;

    private $method;
    private $path;

    /**
     * @var \DC\Router\IController
     */
    private $controller;

    /**
     * @var \DC\Router\IClassFactory
     */
    private $classFactory;

    function __construct($class, $function, $httpMethod, $path, \DC\Router\IClassFactory $classFactory)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("No such class: $class");
        }
        $this->class = $class;
        $this->function = $function;
        $this->method = $httpMethod;
        $this->path = $path;
        $this->classFactory = $classFactory;
    }

    private function getRoutePartsFromComment() {
        $class = new \ReflectionClass($this->class);
        $function = $class->getMethod($this->function);
        $parts = self::getPartsFromReflectionMethod($function);
        $this->function = $parts['function'];
        $this->path = $parts['path'];
        $this->method = $parts['method'];
    }

    /**
     * @return string|null Null means all methods are accepted
     */
    function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * @return \DC\Router\IController|null
     */
    function getController()
    {
        if ($this->controller == null) {
            $this->controller = $this->classFactory->constructClass($this->class);
        }
        return $this->controller;
    }

    /**
     * @return callable
     */
    function getCallable()
    {
        return array($this->getController(), $this->function);
    }

    private static function getPartsFromReflectionMethod(\ReflectionMethod $method) {
        $comment = $method->getDocComment();
        if (preg_match_all('%^\s*\*\s*@route\s+(?:(?P<method>POST|GET|PUT|HEAD)\s+)?(?P<route>/?(?::?[a-z0-9_.()[\]{}=?&]+/?)*\$?)\s*$%im', $comment, $result, PREG_SET_ORDER))
        {
            return array_map(function($r) use ($method) {
                return array(
                    'function' => $method->getName(),
                    'method' => empty($r['method']) ? null : strtoupper($r['method']),
                    'path' => $r['route']
                );
            }, $result);
        } else {
            return array();
        }
    }

    /**
     * @param $className string
     * @param \DC\Router\IClassFactory $classFactory
     * @return IRoute[] Routes
     */
    public static function fromClassName($className, \DC\Router\IClassFactory $classFactory) {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods();
        $parts = array_map(array('\DC\Router\ClassRoute', 'getPartsFromReflectionMethod'), $reflectionMethods);

        $routes = array();
        foreach ($parts as $partRoutes) {
            foreach ($partRoutes as $partRoute) {
                $routes[] = new ClassRoute($className, $partRoute['function'], $partRoute['method'], $partRoute['path'], $classFactory);
            }
        }
        return $routes;
    }
}