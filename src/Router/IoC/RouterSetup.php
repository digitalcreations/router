<?php

namespace DC\Router\IoC;

class RouterSetup {
    /**
     * Set up all the services required by the router for dependency injection.
     *
     * @param \DC\IoC\Container $container
     * @param string[] $controllers
     * @return \DC\Router\Router
     */
    static function setup(\DC\IoC\Container $container, $controllers)
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

        $container->register(function(\DC\Router\ClassRouteFactory $classRouteFactory, \DC\Cache\ICache $cache = null) use ($controllers) {
            return new \DC\Router\DefaultRouteFactory($controllers, $classRouteFactory, $cache);
        })->to('\DC\Router\IRouteFactory')->withContainerLifetime();
        $container->register('\DC\Router\Router')->withContainerLifetime();

        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(\DC\Router\BodyTag::$name, '\DC\Router\BodyTag');

        \DC\JSON\IoC\SerializerSetup::setup($container);

        return $container->resolve('\DC\Router\Router');
    }

    /**
     * Set up all the services required by the router for dependency injection.
     *
     * @param \DC\IoC\Container $container
     * @param string[] $controllers
     * @return \DC\Router\Router
     * @codeCoverageIgnore
     */
    static function route(\DC\IoC\Container $container, $controllers)
    {
        $router = self::setup($container, $controllers);
        $router->route($container->resolve('\DC\Router\IRequest'));
        return $router;
    }

} 