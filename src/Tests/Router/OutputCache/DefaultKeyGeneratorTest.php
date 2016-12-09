<?php

namespace DC\Tests\Router\OutputCache;

class TestCallableClass {
    /**
     * @cache
     */
    function cached() {}

    function uncached() {}

    /**
     * @cache
     */
    function oneParameter($x) {}

    /**
     * @cache
     */
    function twoParameter($x, $y) {}
}

class DefaultKeyGeneratorTest extends \PHPUnit_Framework_TestCase {
    function testAnonymousFunctionWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams(function() {}, []);
        $this->assertStringStartsWith("dcoc_anon_", $key);
    }

    function testClassCallableWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams(['\DC\Tests\Router\OutputCache\TestCallableClass', 'cached'], []);
        $this->assertEquals('dcoc_DC\Tests\Router\OutputCache\TestCallableClass::cached', $key);
    }

    function testObjectCallableWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \DC\Tests\Router\OutputCache\TestCallableClass, 'cached'], []);
        $this->assertEquals('dcoc_DC\Tests\Router\OutputCache\TestCallableClass::cached', $key);
    }

    function testObjectCallableWithParameter() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \DC\Tests\Router\OutputCache\TestCallableClass, 'oneParameter'], ["x" => 1]);
        $this->assertEquals('dcoc_DC\Tests\Router\OutputCache\TestCallableClass::oneParameter?x=1', $key);
    }

    function testObjectCallableWithTwoParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \DC\Tests\Router\OutputCache\TestCallableClass, 'twoParameter'], ["x" => 1, "y" => 2]);
        $this->assertEquals('dcoc_DC\Tests\Router\OutputCache\TestCallableClass::twoParameter?x=1&y=2', $key);
    }
}
 