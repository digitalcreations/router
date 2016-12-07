<?php

namespace DC\Router;

class DefaultRouteFactory implements IRouteFactory {
    /**
     * @var string[]
     */
    private $controllers;
    /**
     * @var ClassRouteFactory
     */
    private $classRouteFactory;

    private $cache;

    /**
     * @param $controllers string[] The controllers to register
     * @param ClassRouteFactory $classRouteFactory
     * @param \DC\Cache\ICache $cache
     */
    function __construct(array $controllers, ClassRouteFactory $classRouteFactory, $cache = null)
    {
        $this->controllers = $controllers;
        $this->classRouteFactory = $classRouteFactory;
        $this->cache = $cache;
    }

    /**
     * @return \DC\Router\IRoute[] All routes
     */
    function getRoutes()
    {
        $getter = function() {
            $routes = array();
            foreach ($this->controllers as $controller) {
                $routes = array_merge($routes, $this->classRouteFactory->routesFromClassName($controller));
            }
            return $routes;
        };

        if ($this->cache != null) {
            return $this->cache->getWithFallback("allRoutes", $getter);
        }
        return $getter();
    }
}