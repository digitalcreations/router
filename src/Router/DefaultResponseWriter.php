<?php

namespace DC\Router;

/**
 * @codeCoverageIgnore
 */
class DefaultResponseWriter implements IResponseWriter {

    function writeResponse(IResponse $response)
    {
        header(StatusCodes::httpHeaderFor($response->getStatusCode()));
        foreach ($response->getCustomHeaders() as $name => $value) {
            header($name.': '.$value);
        }
        echo $response->getContent();
    }
}