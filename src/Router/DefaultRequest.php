<?php

namespace DC\Router;
use string;

/**
 * @codeCoverageIgnore
 */
class DefaultRequest implements IRequest {

    /**
     * @return string HTTP method, normalized to uppercase
     */
    function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return string The path part of the request.
     */
    function getPath()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Return an array of parameters provided with the request. A good candidate here would be to pass a union of
     * $_GET and $_POST.
     *
     * @return string[string] string to string map of parameter names and values
     */
    function getRequestParameters()
    {
        return $_GET + $_POST;
    }

    /**
     * @return string[] Array of headers.
     */
    function getHeaders()
    {
        return getallheaders();
    }

    /**
     * Returns the body (POST or PUT) as a string. For empty requests (e.g. GET and DELETE), this returns null.
     *
     * @return string
     */
    function getBody()
    {
        return file_get_contents('php://input');
    }
}