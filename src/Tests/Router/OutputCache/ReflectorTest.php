<?php

namespace DC\Tests\Router\OutputCache;

/**
 * @covers \DC\Router\OutputCache\Reflector
 */
class ReflectorTest extends \PHPUnit_Framework_TestCase {
    function testHandlesClassCallableArray() {
        $reflector = new \DC\Router\OutputCache\Reflector();
        $method = $reflector->getReflectionFunctionForCallable(['\DC\Tests\Router\OutputCache\ReflectorTest', 'testHandlesClassCallableArray']);
        $this->assertInstanceOf('\ReflectionMethod', $method);
    }

    function testHandlesClassCallableString() {
        $reflector = new \DC\Router\OutputCache\Reflector();
        $method = $reflector->getReflectionFunctionForCallable('\DC\Tests\Router\OutputCache\ReflectorTest::testHandlesClassCallableString');
        $this->assertInstanceOf('\ReflectionMethod', $method);
    }

    function testHandlesObjectCallableArray() {
        $reflector = new \DC\Router\OutputCache\Reflector();
        $method = $reflector->getReflectionFunctionForCallable([$this, 'testHandlesObjectCallableArray']);
        $this->assertInstanceOf('\ReflectionMethod', $method);
    }

    function testHandlesStringCallable() {
        $reflector = new \DC\Router\OutputCache\Reflector();
        $function = $reflector->getReflectionFunctionForCallable('strtotime');
        $this->assertInstanceOf('\ReflectionFunction', $function);
    }

    function testHandlesAnonymousFunction() {
        $reflector = new \DC\Router\OutputCache\Reflector();
        $function = $reflector->getReflectionFunctionForCallable(function() {});
        $this->assertInstanceOf('\ReflectionFunction', $function);
    }
}
 