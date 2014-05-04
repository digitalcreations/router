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

    function constructClass($name)
    {
        return $this->container->resolve($name);
    }
}