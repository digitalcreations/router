<?php

namespace DC\Router\Exceptions;

class RouteNotFoundException extends \Exception {
    function __construct(\DC\Router\IRequest $request) {
        parent::__construct("Route was not found for request: " . $request->getMethod(). ' ' .$request->getPath());
    }
} 