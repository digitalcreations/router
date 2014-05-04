<?php
/**
 * Created by PhpStorm.
 * User: Vegard
 * Date: 5/2/14
 * Time: 9:50 AM
 */

namespace DC\Router\Exceptions;


class ContentFoundWithNoContentStatusCodeException extends \Exception {
    function __construct(\DC\Router\IResponse $response) {
        parent::__construct("You tried to set the content when the response had a no-content status code: " . $response->getStatusCode());
    }
} 