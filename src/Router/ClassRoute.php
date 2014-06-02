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

    function __construct($class, $function, $httpMethod, $path)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("No such class: $class");
        }
        $this->class = $class;
        $this->function = $function;
        $this->method = $httpMethod;
        $this->path = $path;
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
     * @return callable
     */
    function getCallable()
    {
        return array($this->class, $this->function);
    }
}