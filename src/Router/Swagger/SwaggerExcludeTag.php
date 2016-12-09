<?php

namespace DC\Router\Swagger;


class SwaggerExcludeTag extends \phpDocumentor\Reflection\DocBlock\Tag {
    public static $name = 'swagger-exclude';

    public function __toString()
    {
        return (string)$this->description;
    }
}