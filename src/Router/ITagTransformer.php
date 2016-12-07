<?php
namespace DC\Router;

/**
 * An interface used for marking phpDocumentor tags in a route that is interesting for the router.
 */
interface ITagTransformer
{
    /**
     * @return mixed A value you wish to be available on the registered route.
     */
    function getValueForRoute();
}