<?php
namespace DC\Router\Exceptions;

class ResponseContentIsNotStringException extends \Exception {
    function __construct() {
        parent::__construct("The generated response content is not a string.");
    }
} 