<?php

namespace DC\Tests\Router\OutputCache;

class ParameterPreparerHelper {
    /**
     * @cache-exclude $y
     */
    function oneParameterVaries($x, $y) {}

    /**
     * @cache-state foo
     */
    function oneState() {}

    function noParams() {}
}

/**
 * @covers \DC\Router\OutputCache\ParameterPreparer
 */
class ParameterPreparerTest extends \PHPUnit_Framework_TestCase {
    function testNoParams() {
        $preparer = new \DC\Router\OutputCache\ParameterPreparer(new \DC\Router\OutputCache\Reflector(), []);
        $arr = $preparer->prepareParameters(['\DC\Tests\Router\OutputCache\ParameterPreparerHelper', 'noParams'], []);
        $this->assertCount(0, $arr);
    }

    function testBasicVaryBy() {
        $preparer = new \DC\Router\OutputCache\ParameterPreparer(new \DC\Router\OutputCache\Reflector(), []);
        $arr = $preparer->prepareParameters(['\DC\Tests\Router\OutputCache\ParameterPreparerHelper', 'oneParameterVaries'], ['x' => 1, 'y' => 2]);
        $this->assertCount(1, $arr);
        $this->assertEquals(1, $arr['x']);
    }

    function testVaryByWithVariationState() {
        $mockState = $this->getMock('\DC\Router\OutputCache\IStateProvider');
        $mockState
            ->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $mockState
            ->expects($this->once())
            ->method('getCurrentState')
            ->willReturn('bar');

        $preparer = new \DC\Router\OutputCache\ParameterPreparer(new \DC\Router\OutputCache\Reflector(),
            [$mockState]);
        $arr = $preparer->prepareParameters(['\DC\Tests\Router\OutputCache\ParameterPreparerHelper', 'oneState'], []);
        $this->assertCount(1, $arr);
        $this->assertEquals('bar', $arr['foo']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testInvalidStateProvider() {
        $preparer = new \DC\Router\OutputCache\ParameterPreparer(new \DC\Router\OutputCache\Reflector(), []);
        $preparer->prepareParameters(['\DC\Tests\Router\OutputCache\ParameterPreparerHelper', 'oneState'], []);
    }
}
 