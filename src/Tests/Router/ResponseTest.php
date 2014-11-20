<?php

namespace DC\Tests\Router;

class ResponseTest extends \PHPUnit_Framework_TestCase {
    function testContentSetPersistsValue() {
        $response = new \DC\Router\Response();
        $response->setContent("foo");

        $this->assertEquals("foo", $response->getContent());
    }

    function testStatusCodeSetPersistsValue() {
        $response = new \DC\Router\Response();
        $response->setStatusCode(\DC\Router\StatusCodes::HTTP_NO_CONTENT);

        $this->assertEquals(\DC\Router\StatusCodes::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    function testDefaultStatusCodeIsOK() {
        $response = new \DC\Router\Response();
        $this->assertEquals(\DC\Router\StatusCodes::HTTP_OK, $response->getStatusCode());
    }

    function testContentTypePersistsValue() {
        $response = new \DC\Router\Response();
        $response->setContentType("text/xml");

        $this->assertEquals("text/xml", $response->getContentType());
    }

    /**
     * @expectedException \DC\Router\Exceptions\ContentFoundWithNoContentStatusCodeException
     */
    function testSetStatusCodeThrowsIfContentSetForNoContentCode() {
        $response = new \DC\Router\Response();
        $response->setContent("foo");
        $response->setStatusCode(\DC\Router\StatusCodes::HTTP_NO_CONTENT);
    }

    /**
     * @expectedException \DC\Router\Exceptions\ContentFoundWithNoContentStatusCodeException
     */
    function testSetContentThrowsIfStatusCodeIndicatesNoContent() {
        $response = new \DC\Router\Response();
        $response->setStatusCode(\DC\Router\StatusCodes::HTTP_NO_CONTENT);
        $response->setContent("foo");
    }

    function testSetCustomHeader() {
        $response = new \DC\Router\Response();
        $response->setCustomHeader("X-Foo", "bar");
        $this->assertEquals(array("X-Foo" => "bar"), $response->getCustomHeaders());
    }

    function testSetCustomHeaderOverwritesExisting() {
        $response = new \DC\Router\Response();
        $response->setCustomHeader("X-Foo", "bar");
        $response->setCustomHeader("X-Baz", "untouched");

        $response->setCustomHeaders(array("X-Foo" => "foo", "X-Bar" => "bar"));

        $headers = $response->getCustomHeaders();
        $this->assertEquals(array(
            "X-Foo" => "foo",
            "X-Bar" => "bar",
            "X-Baz" => "untouched"
        ), $headers);
    }

    function testRemoveCustomHeader() {
        $response = new \DC\Router\Response();
        $response->setCustomHeader("X-Foo", "bar");
        $response->removeCustomHeader("X-Foo");
        $this->assertEquals(array(), $response->getCustomHeaders());
    }

    function testClearCustomHeaders() {
        $response = new \DC\Router\Response();
        $response->setCustomHeader("X-Foo", "bar");
        $response->setContentType("text/plain");

        $response->clearCustomHeaders();
        $this->assertEquals(array("Content-Type" => "text/plain; charset=utf-8"), $response->getCustomHeaders());
    }
}
 