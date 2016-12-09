<?php

namespace DC\Router\IoC;

class Module extends \DC\IoC\Modules\Module {
    /**
     * @var array|\string[]
     */
    private $controllers;
    /**
     * @var ModuleOptions
     */
    private $options;

    /**
     * Module constructor.
     * @param array|\string[] $controllers List of controller names
     * @param ModuleOptions $options
     */
    public function __construct(array $controllers, ModuleOptions $options = null)
    {
        parent::__construct("dc/router", ["dc/json", "dc/cache"]);
        $this->controllers = $controllers;
        $this->options = $options ?? new ModuleOptions();
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

        if ($this->options->isOutputCacheEnabled()) {
            $container
                ->register('\DC\Router\OutputCache\DefaultKeyGenerator')
                ->to('\DC\Router\OutputCache\IKeyGenerator');
            $container
                ->register('\DC\Router\OutputCache\CacheFilter')
                ->to('\DC\Router\IGlobalFilter');
        }
    }
}