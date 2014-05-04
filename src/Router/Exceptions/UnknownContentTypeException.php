<?php

namespace DC\Router\Exceptions;

class UnknownContentTypeException extends \Exception {
    function __construct($contentType) {
        parent::__construct("Content could not be decoded, received: " . $contentType);
    }
} 