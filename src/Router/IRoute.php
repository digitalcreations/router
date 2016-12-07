<?php

namespace DC\Router;

interface IRoute {
    /**
     * The HTTP method this route accepts.
     *
     * @return string|null Null means all methods are accepted
     */
    function getMethod();

    /**
     * The path (with placeholders) this route accepts.
     *
     * @return string
     */
    function getPath();

    /**
     * The method to call when this route is invoked.
     *
     * @return callable
     */
    function getCallable();

    /**
     * Get the data with IRouteTag-implementing tags as an associative array.
     *
     * Note that if multiple tags with the same name is listed, the value will be an array, for instance:
     * @route GET /foo
     * @route POST /bar
     * will result in this result:
     * [
     *  "route" => [RouteSpecification("GET", "/foo"), RouteSpecification("POST", "/bar")]
     * ]
     *
     * @return mixed[] String-indexed array that contains values as supplied by the phpDocumentor tags.
     */
    function getTagValues();
}