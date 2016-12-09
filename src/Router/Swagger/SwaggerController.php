<?php

namespace DC\Router\Swagger;

class SwaggerController extends \DC\Router\JsonController {

    /**
     * @var Options
     */
    private $options;
    /**
     * @var \DC\Router\IRouteFactory
     */
    private $routeFactory;
    /**
     * @var SwaggerDefinitionBuilder
     */
    private $builder;

    function __construct(\DC\Router\Swagger\Options $options, \DC\Router\IRouteFactory $routeFactory, \DC\Router\Swagger\SwaggerDefinitionBuilder $builder)
    {
        $this->options = $options;
        $this->routeFactory = $routeFactory;
        $this->builder = $builder;
    }

    /**
     * @swagger-exclude
     * @route GET /swagger
     */
    function getSpecification() {
        header('Access-Control-Allow-Origin: *');
        return $this->builder->build($this->routeFactory->getRoutes());
    }
}