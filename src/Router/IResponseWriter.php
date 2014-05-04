<?php

namespace DC\Router;

interface IResponseWriter {
    function writeResponse(IResponse $response);
} 