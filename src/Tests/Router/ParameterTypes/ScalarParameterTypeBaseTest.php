<?php

namespace DC\Tests\Router\ParameterTypes;

class ScalarParameterType extends \DC\Router\ParameterTypes\ScalarParameterTypeBase {

}

class ScalarParameterTypeBaseTest extends \PHPUnit_Framework_TestCase {
    function testTransformValue() {
        $type = new ScalarParameterType('int', 'y');
        $this->assertTrue(123 === $type->transformValue('123'));
    }

    function testTypeAndRegularExpression() {
        $type = new ScalarParameterType('int', '-?\d+');
        $this->assertEquals('int', $type->getType());
        $this->assertEquals('-?\d+', $type->getRegularExpression());
    }
}
 