<?php

namespace DC\Tests\Router\IoC;

use DC\Router\IResponse;

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

    function writeResponse(IResponse $response)
    {
        $this->response = $response;
    }
}

class RouterSetupTest extends \PHPUnit_Framework_TestCase {
    function testSetup() {
        $container = new \DC\IoC\Container();
        $router = \DC\Router\IoC\RouterSetup::setup($container, array());
        $this->assertInstanceOf('\DC\Router\Router', $router);
    }

    function testSetupRegistersRequiredClasses() {
        $container = new \DC\IoC\Container();

        \DC\Router\IoC\RouterSetup::setup($container, array());

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
    }

    function testParameterType() {
        $mockParameterType = $this->getMock('\DC\Router\IParameterType');
        $mockParameterType
            ->expects($this->any())
            ->method('getType')
            ->willReturn('user');
        $mockParameterType
            ->expects($this->any())
            ->method('getRegularExpression')
            ->willReturn('\d+');
        $mockParameterType
            ->expects($this->any())
            ->method('transformValue')
            ->willReturn(new \stdClass);

        $container = new \DC\IoC\Container();
        $container->register($mockParameterType)->to('\DC\Router\IParameterType');

        // copied from \DC\Router\IoC\RouterSetup::setup (and modified to use a mock response writer)
        $container->register(new \DC\Router\IoC\ClassFactory($container))->to('\DC\Router\IClassFactory');
        $container->register('\DC\Router\DefaultRouteMatcher')->to('\DC\Router\IRouteMatcher')->withContainerLifetime();
        $container->register('\DC\Tests\Router\IoC\MockResponseWriter')->to('\DC\Router\IResponseWriter')->withContainerLifetime();
        $container->register('\DC\Router\DefaultParameterTypeFactory')->to('\DC\Router\IParameterTypeFactory')->withContainerLifetime();
        $container->register('\DC\Router\DefaultRequest')->to('\DC\Router\IRequest')->withContainerLifetime();

        $container->register(function(\DC\Router\IClassFactory $classFactory) {
            return new \DC\Router\DefaultRouteFactory(array('\DC\Tests\Router\IoC\ControllerTest'), $classFactory);
        })->to('\DC\Router\IRouteFactory')->withContainerLifetime();
        $container->register('\DC\Router\Router')->withContainerLifetime();

        $router = $container->resolve('\DC\Router\Router');
        // end copy

        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn('GET');
        $mockRequest
            ->expects($this->any())
            ->method('getPath')
            ->willReturn('/user/3');
        $router->route($mockRequest);

        $responseWriter = $container->resolve('\DC\Router\IResponseWriter');
        $this->assertEquals("vegard", $responseWriter->response->getContent());
        $this->assertEquals("text/html", $responseWriter->response->getContentType());
    }
}
 