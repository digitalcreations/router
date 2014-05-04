<?php

namespace DC\Router\Exceptions;

class MultipleRoutesFoundException extends \Exception {
    function __construct(\DC\Router\IRequest $request) {
        parent::__construct("Multiple routes were found for request: " . $request->getMethod(). ' ' .$request->getPath());
    }
} 