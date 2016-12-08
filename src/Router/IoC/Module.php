<?php

namespace DC\Router\IoC;

class Module extends \DC\IoC\Modules\Module {
    /**
     * @var array|\string[]
     */
    private $controllers;

    /**
     * Module constructor.
     * @param string[] $controllers List of controller names
     */
    public function __construct(array $controllers)
    {
        parent::__construct("dc/router", ["dc/json", "dc/cache"]);
        $this->controllers = $controllers;
    }

    /**
     * @param \DC\IoC\Container $container
     * @return null
     */
    function register(\DC\IoC\Container $container)
    {
        $container->register(new ClassFactory($container))->to('\DC\Router\IClassFactory');
        $container->register('\DC\Router\DefaultRouteMatcher')->to('\DC\Router\IRouteMatcher')->withContainerLifetime();
        $container->register('\DC\Router\DefaultResponseWriter')->to('\DC\Router\IResponseWriter')->withContainerLifetime();
        $container->register('\DC\Router\DefaultParameterTypeFactory')->to('\DC\Router\IParameterTypeFactory')->withContainerLifetime();
        $container->register('\DC\Router\DefaultRequest')->to('\DC\Router\IRequest')->withContainerLifetime();

        $container->register('\DC\Router\ParameterTypes\BoolParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();
        $container->register('\DC\Router\ParameterTypes\FloatParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();
        $container->register('\DC\Router\ParameterTypes\IntParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();
        $container->register('\DC\Router\ParameterTypes\StringParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();

        $controllers = $this->controllers;

        $container->register(function(\DC\Router\ClassRouteFactory $classRouteFactory, \DC\Cache\ICache $cache = null) use ($controllers) {
            return new \DC\Router\DefaultRouteFactory($controllers, $classRouteFactory, $cache);
        })->to('\DC\Router\IRouteFactory')->withContainerLifetime();
        $container->register('\DC\Router\Router')->withContainerLifetime();

        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(\DC\Router\BodyTag::$name, '\DC\Router\BodyTag');
    }
}