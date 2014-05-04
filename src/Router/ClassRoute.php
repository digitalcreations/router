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
     * @var \DC\Router\IClassFactory
     */
    private $classFactory;

    function __construct($class, $function, \DC\Router\IClassFactory $classFactory)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("No such class: $class");
        }
        $this->class = $class;
        $this->function = $function;
        $this->classFactory = $classFactory;

        $this->getRoutePartsFromComment();
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
        return $this->classFactory->constructClass($this->class);
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
        if (preg_match('%^\s*\*\s*@route\s+(?:(?P<method>POST|GET|PUT|HEAD)\s+)?(?P<route>/?(?::?[a-z0-9_.()[\]{}]+/?)*\$?)\s*$%im', $comment, $result))
        {
            return array(
                'function' => $method->getName(),
                'method' => empty($result['method']) ? null : strtoupper($result['method']),
                'path' => $result['route']
            );
        } else {
            return null;
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
        $eligibleMethods = array_filter($reflectionMethods, function($method) {
            return self::getPartsFromReflectionMethod($method) != null;
        });
        return array_map(function($method) use ($className, $classFactory) {
            return new ClassRoute($className, $method->getName(), $classFactory);
        }, $eligibleMethods);
    }
}