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

    function testReturnsRegisteredTags() {
        $factory = new \DC\Router\ClassRouteFactory(array());
        $routes = $factory->routesFromClassName('\DC\Tests\Router\ClassRouteTestController');
        $this->assertCount(2, $routes);
        $values = $routes[0]->getTagValues();
        $this->assertTrue(is_array($values[\DC\Router\RouteTagTransformer::NAME]));
        $this->assertInstanceOf('\DC\Router\RouteSpecification', $values[\DC\Router\RouteTagTransformer::NAME][0]);
        $this->assertEquals('GET', $values[\DC\Router\RouteTagTransformer::NAME][0]->getMethod());
        $this->assertEquals('/foo', $values[\DC\Router\RouteTagTransformer::NAME][0]->getPath());
        $this->assertInstanceOf('\DC\Router\RouteSpecification', $values[\DC\Router\RouteTagTransformer::NAME][1]);
        $this->assertEquals('GET', $values[\DC\Router\RouteTagTransformer::NAME][1]->getMethod());
        $this->assertEquals('/bar', $values[\DC\Router\RouteTagTransformer::NAME][1]->getPath());
    }
}
 