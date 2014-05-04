<?php

namespace DC\Tests\Router;

class StatusCodesTest extends \PHPUnit_Framework_TestCase {
    function testCanHaveBody() {
        $this->assertTrue(\DC\Router\StatusCodes::canHaveBody(\DC\Router\StatusCodes::HTTP_OK));
        $this->assertTrue(\DC\Router\StatusCodes::canHaveBody(\DC\Router\StatusCodes::HTTP_TEMPORARY_REDIRECT));

        $this->assertFalse(\DC\Router\StatusCodes::canHaveBody(\DC\Router\StatusCodes::HTTP_CONTINUE));
        $this->assertFalse(\DC\Router\StatusCodes::canHaveBody(\DC\Router\StatusCodes::HTTP_NO_CONTENT));
        $this->assertFalse(\DC\Router\StatusCodes::canHaveBody(\DC\Router\StatusCodes::HTTP_NOT_MODIFIED));
    }

    function testGetMessageForCode() {
        $reflectionClass = new \ReflectionClass('\DC\Router\StatusCodes');
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant => $value) {
            if (strpos($constant, 'HTTP_') !== 0) continue;
            $message = \DC\Router\StatusCodes::getMessageForCode($value);
            // message starts with error code
            $this->assertTrue(strpos($message, $value."") === 0);
        }
    }

    function testHttpHeaderFor() {
        $this->assertEquals("HTTP/1.1 404 Not Found", \DC\Router\StatusCodes::httpHeaderFor(\DC\Router\StatusCodes::HTTP_NOT_FOUND));
    }

    function testIsError() {
        $reflectionClass = new \ReflectionClass('\DC\Router\StatusCodes');
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant => $value) {
            if (strpos($constant, 'HTTP_') !== 0) continue;
            $isError = \DC\Router\StatusCodes::isError($value);
            if ($value < 400) {
                $this->assertFalse($isError, $constant . " is not an error code");
            } else {
                $this->assertTrue($isError, $constant . " is an error code");
            }
        }
    }
}
 