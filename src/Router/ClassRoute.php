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
     * @var ITagTransformer[]
     */
    private $tags;

    function __construct($class, $function, $httpMethod, $path, array $tags = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("No such class: $class");
        }
        $this->class = $class;
        $this->function = $function;
        $this->method = $httpMethod;
        $this->path = $path;
        $this->tags = $tags;
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

    /**
     * Get the data with IRouteTag-implementing tags as an associative array.
     *
     * @return mixed[] String-indexed array that contains values as supplied by the phpDocumentor tags
     */
    function getTagValues()
    {
        $values = [];
        foreach ($this->tags as $tag) {
            if (isset($values[$tag->getName()]) && !is_array($values[$tag->getName()])) {
                $values[$tag->getName()] = [$values[$tag->getName()]];
            }

            if (isset($values[$tag->getName()]) && is_array($values[$tag->getName()])) {
                $values[$tag->getName()][] = $tag->getValueForRoute();
            }
            else {
                $values[$tag->getName()] = $tag->getValueForRoute();
            }
        }
        return $values;
    }
}