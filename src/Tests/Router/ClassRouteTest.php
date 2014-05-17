<?php

namespace DC\Tests\Router;

class ClassRouteTestController extends \DC\Router\ControllerBase {
    public $invoked = false;
    /**
     * @route GET /foo
     * @route GET /bar
     */
    public function route() {
        $this->invoked = true;
    }

    public function notRouted() { }
}

class ClassRouteTest extends \PHPUnit_Framework_TestCase {
    function testSetup() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');

        $routes = \DC\Router\ClassRoute::fromClassName('\DC\Tests\Router\ClassRouteTestController', $mockClassFactory);

        $this->assertEquals('GET', $routes[0]->getMethod());
        $this->assertEquals('/foo', $routes[0]->getPath());
        $this->assertEquals('GET', $routes[1]->getMethod());
        $this->assertEquals('/bar', $routes[1]->getPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsOnUnqualifiedClassName() {
        new \DC\Router\ClassRoute('SomeRoutable', 'route', 'GET', '/bar', $this->getMock('\DC\Router\IClassFactory'));
    }

    function testFromClassNamePicksOnlyDecoratedMethods() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');

        $routes = \DC\Router\ClassRoute::fromClassName('\DC\Tests\Router\ClassRouteTestController', $mockClassFactory);
        $this->assertEquals(2, count($routes));
    }

    function testResolvesRouteGroupUsingContainer() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');
        $mockClassFactory
            ->expects($this->once())
            ->method('constructClass')
            ->with($this->equalTo('\DC\Tests\Router\ClassRouteTestController'))
            ->willReturn(new ClassRouteTestController());

        $route = new \DC\Router\ClassRoute('\DC\Tests\Router\ClassRouteTestController', 'route', 'GET', '/bar', $mockClassFactory);
        $route->getController();
    }

    function testResolvesCallableUsingContainer() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');
        $mockClassFactory
            ->expects($this->once())
            ->method('constructClass')
            ->with($this->equalTo('\DC\Tests\Router\ClassRouteTestController'))
            ->willReturn(new ClassRouteTestController());

        $route = new \DC\Router\ClassRoute('\DC\Tests\Router\ClassRouteTestController', 'route', 'GET', '/bar', $mockClassFactory);
        $callable = $route->getCallable();

        $this->assertInstanceOf('\DC\Tests\Router\ClassRouteTestController', $callable[0]);
        $this->assertEquals('route', $callable[1]);
    }
}