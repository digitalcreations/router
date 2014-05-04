<?php

namespace DC\Router;

interface IRouteFactory {
    /**
     * @return \DC\Router\IRoute[] All routes
     */
    function getRoutes();
} 