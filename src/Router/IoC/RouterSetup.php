<?php

namespace DC\Router\IoC;

class RouterSetup {
    /**
     * Set up all the services required by the router for dependency injection.
     *
     * @param \DC\IoC\Container $container
     * @param string[] $controllers
     * @return \DC\IoC\Router
     */
    static function setup(\DC\IoC\Container $container, $controllers)
    {

        $container->register(new ClassFactory($container))->to('\DC\Router\IClassFactory');
        $container->register('\DC\Router\DefaultRouteMatcher')->to('\DC\Router\IRouteMatcher')->withContainerLifetime();
        $container->register('\DC\Router\DefaultResponseWriter')->to('\DC\Router\IResponseWriter')->withContainerLifetime();
        $container->register('\DC\Router\DefaultParameterTypeFactory')->to('\DC\Router\IParameterTypeFactory')->withContainerLifetime();
        $container->register('\DC\Router\DefaultRequest')->to('\DC\Router\IRequest')->withContainerLifetime();

        $container->register(function(\DC\Router\IClassFactory $classFactory) use ($controllers) {
            return new \DC\Router\DefaultRouteFactory($controllers, $classFactory);
        })->to('\DC\Router\IRouteFactory')->withContainerLifetime();
        $container->register('\DC\Router\Router')->withContainerLifetime();

        return $container->resolve('\DC\Router\Router');
    }

    /**
     * Set up all the services required by the router for dependency injection.
     *
     * @param \DC\IoC\Container $container
     * @param string[] $controllers
     * @return \DC\IoC\Router
     * @codeCoverageIgnore
     */
    static function route(\DC\IoC\Container $container, $controllers)
    {
        $router = self::setup($container, $controllers);
        $router->route($container->resolve('\DC\Router\IRequest'));
        return $router;
    }

} 