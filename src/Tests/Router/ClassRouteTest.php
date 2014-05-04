<?php

namespace DC\Tests\Router;

class ClassRouteTestController extends \DC\Router\ControllerBase {
    public $invoked = false;
    /**
     * @route GET /foo
     */
    public function route() {
        $this->invoked = true;
    }

    public function notRouted() { }
}

class ClassRouteTest extends \PHPUnit_Framework_TestCase {
    function testSetup() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');

        $route = new \DC\Router\ClassRoute('\DC\Tests\Router\ClassRouteTestController', 'route', $mockClassFactory);
        $this->assertEquals('GET', $route->getMethod());
        $this->assertEquals('/foo', $route->getPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsOnUnqualifiedClassName() {
        new \DC\Router\ClassRoute('SomeRoutable', 'route', $this->getMock('\DC\Router\IClassFactory'));
    }

    function testFromClassNamePicksOnlyDecoratedMethods() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');

        $routes = \DC\Router\ClassRoute::fromClassName('\DC\Tests\Router\ClassRouteTestController', $mockClassFactory);
        $this->assertEquals(1, count($routes));
    }

    function testResolvesRouteGroupUsingContainer() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');
        $mockClassFactory
            ->expects($this->once())
            ->method('constructClass')
            ->with($this->equalTo('\DC\Tests\Router\ClassRouteTestController'))
            ->willReturn(new ClassRouteTestController());

        $route = new \DC\Router\ClassRoute('\DC\Tests\Router\ClassRouteTestController', 'route', $mockClassFactory);
        $route->getController();
    }

    function testResolvesCallableUsingContainer() {
        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');
        $mockClassFactory
            ->expects($this->once())
            ->method('constructClass')
            ->with($this->equalTo('\DC\Tests\Router\ClassRouteTestController'))
            ->willReturn(new ClassRouteTestController());

        $route = new \DC\Router\ClassRoute('\DC\Tests\Router\ClassRouteTestController', 'route', $mockClassFactory);
        $callable = $route->getCallable();

        $this->assertInstanceOf('\DC\Tests\Router\ClassRouteTestController', $callable[0]);
        $this->assertEquals('route', $callable[1]);
    }
}