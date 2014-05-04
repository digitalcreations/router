<?php

namespace DC\Router;

class DefaultRouteFactory implements IRouteFactory {
    /**
     * @var string[]
     */
    private $controllers;
    /**
     * @var \DC\IoC\IClassFactory
     */
    private $classFactory;

    /**
     * @param $controllers string[] The controllers to register
     * @param \DC\Router\IClassFactory $classFactory
     */
    function __construct(array $controllers, \DC\Router\IClassFactory $classFactory)
    {
        $this->controllers = $controllers;
        $this->classFactory = $classFactory;
    }

    /**
     * @return \DC\Router\IRoute[] All routes
     */
    function getRoutes()
    {
        $routes = array();
        foreach ($this->controllers as $controller) {
            $routes += ClassRoute::fromClassName($controller, $this->classFactory);
        }
        return $routes;
    }
}