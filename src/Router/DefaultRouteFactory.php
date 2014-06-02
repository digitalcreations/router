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
     * @param $cache array|\DC\Cache\ICache[]
     */
    function __construct(array $controllers, ClassRouteFactory $classRouteFactory, $cache = array())
    {
        $this->controllers = $controllers;
        $this->classRouteFactory = $classRouteFactory;
        if (count($cache) > 0) {
            $this->cache = $cache[0];
        }
    }

    private function actualGetRoutes() {
        $routes = array();
        foreach ($this->controllers as $controller) {
            $routes = array_merge($routes, $this->classRouteFactory->routesFromClassName($controller));
        }
        return $routes;
    }

    /**
     * @return \DC\Router\IRoute[] All routes
     */
    function getRoutes()
    {
        if ($this->cache != null) {
            return $this->cache->getWithFallback("allRoutes", array($this, 'actualGetRoutes'));
        }
        return $this->actualGetRoutes();
    }
}