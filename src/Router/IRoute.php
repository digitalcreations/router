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
     * The controller this belongs to, for pre- and post-processing.
     *
     * Can return null when the route is not part of a controller.
     *
     * @return \DC\Router\IController|null
     */
    function getController();

    /**
     * The method to call when this route is invoked.
     *
     * @return callable
     */
    function getCallable();
}