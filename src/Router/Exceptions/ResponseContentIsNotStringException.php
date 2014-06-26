<?php
namespace DC\Router\Exceptions;

class ResponseContentIsNotStringException extends \Exception {
    function __construct(\DC\Router\IRequest $request) {
        parent::__construct(
            sprintf("The generated response content is not a string when requesting %s %s.", $request->getMethod(), $request->getPath()));
    }
} 