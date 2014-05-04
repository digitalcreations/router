<?php

namespace DC\Router;

interface IRequest {
    /**
     * @return string HTTP method, normalized to uppercase
     */
    function getMethod();

    /**
     * @return string The path part of the request.
     */
    function getPath();

    /**
     * Return an array of parameters provided with the request. A good candidate here would be to pass a union of
     * $_GET and $_POST.
     *
     * @return string[string] string to string map of parameter names and values
     */
    function getRequestParameters();

    /**
     * Returns an array of all the header values.
     *
     * @return array Array of headers.
     */
    function getHeaders();

    /**
     * Returns the body (POST or PUT) as a string. For empty requests (e.g. GET and DELETE), this returns null.
     *
     * @return string
     */
    function getBody();
}