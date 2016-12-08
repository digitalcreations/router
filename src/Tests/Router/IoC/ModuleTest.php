<?php

namespace DC\Tests\Router\IoC;

class ControllerTest extends \DC\Router\ControllerBase
{
    function __construct(\DC\Router\Router $router)
    {

    }

    /**
     * @route GET /user
     */
    function getUserList() {
        return json_encode(array(1,2,3));
    }

    /**
     * @route GET /user/{user:user}
     * @param \stdClass $user
     * @return \stdClass
     */
    function getUser(\stdClass $user) {
        return "vegard";
    }
}

class MockResponseWriter implements \DC\Router\IResponseWriter {

    public $response;

    function writeResponse(\DC\Router\IResponse $response)
    {
        $this->response = $response;
    }
}

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    function testBasicSetup() {
        $container = new \DC\IoC\Container();

        $container->registerModules([
            new \DC\Router\IoC\Module([]),
            new \DC\Cache\Module(),
            new \DC\JSON\IoC\Module()
        ]);

        $router = $container->resolve('\DC\Router\Router');
        $this->assertInstanceOf('\DC\Router\Router', $router);
    }

    function testSetupRegistersRequiredClasses() {
        $container = new \DC\IoC\Container();

        $container->registerModules([
            new \DC\Router\IoC\Module([]),
            new \DC\Cache\Module(),
            new \DC\JSON\IoC\Module()
        ]);

        $interfaces  = array(
            '\DC\Router\IClassFactory',
            '\DC\Router\IRouteMatcher',
            '\DC\Router\IResponseWriter',
            '\DC\Router\IParameterTypeFactory',
            '\DC\Router\IRequest',
            '\DC\Router\IRouteFactory',
            '\DC\Router\Router'
        );

        foreach ($interfaces as $interface) {
            $instance = $container->resolve($interface);
            $this->assertInstanceOf($interface, $instance);

            $this->assertTrue($instance === $container->resolve($interface));
        }

        $this->assertCount(4, $container->resolveAll('\DC\Router\IParameterType'));
    }
}
