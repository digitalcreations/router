<?php

namespace DC\Router\IoC;

class ClassFactory implements \DC\Router\IClassFactory {

    /**
     * @var \DC\IoC\Container
     */
    private $container;

    function __construct(\DC\IoC\Container $container)
    {
        $this->container = $container;
    }

    function resolve($name)
    {
        return $this->container->resolve($name);
    }

    function resolveAll($name) {
        return $this->container->resolveAll($name);
    }
}