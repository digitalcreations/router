<?php

namespace DC\Tests\Router;

class ClassRouteFactoryTest extends \PHPUnit_Framework_TestCase {
    function testFromClassNamePicksOnlyDecoratedMethods() {
        $factory = new \DC\Router\ClassRouteFactory(array());
        $routes = $factory->routesFromClassName('\DC\Tests\Router\ClassRouteTestController');
        $this->assertEquals(2, count($routes));
    }

    function testSetup() {
        $factory = new \DC\Router\ClassRouteFactory(array());
        $routes = $factory->routesFromClassName('\DC\Tests\Router\ClassRouteTestController');

        $this->assertEquals('GET', $routes[0]->getMethod());
        $this->assertEquals('/foo', $routes[0]->getPath());
        $this->assertEquals('GET', $routes[1]->getMethod());
        $this->assertEquals('/bar', $routes[1]->getPath());
    }
}
 