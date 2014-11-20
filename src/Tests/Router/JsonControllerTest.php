<?php

namespace DC\Tests\Router;

//class MockJsonController extends \DC\Router\JsonController {
//    /**
//     * @route /foo/{id}
//     */
//    function getFoo($id) {
//        return array(
//            "id" => (int)$id,
//            "name" => "foo"
//        );
//    }
//}

class JsonControllerTest extends \PHPUnit_Framework_TestCase {
    function testAfterRouteSerialization() {
        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn(array('Accept' => 'application/json'));

        $controller = new \DC\Router\JsonController();
        $controller->formatNegotiator = new \Negotiation\FormatNegotiator();
        $controller->setRequest($mockRequest);

        $response = new \DC\Router\Response();
        $response->setContentType('text/html');
        $response->setContent(array(
            "id" => 23,
            "name" => "foo"
        ));

        $controller->afterRoute(array('id' => 23), $response);
        $this->assertEquals('{"id":23,"name":"foo"}', $response->getContent());
    }

    function testParseInput() {
        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('{"id":23,"name":"foo"}');
        $mockRequest
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn(array('Content-Type' => 'application/json'));

        $controller = new \DC\Router\JsonController();
        $controller->setRequest($mockRequest);

        $deserialized = $controller->getRequestBodyAsObject();
        $this->assertEquals(23, $deserialized->id);
        $this->assertEquals('foo', $deserialized->name);
    }

    function testParseInputWithCharset() {
        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('{"id":23,"name":"foo"}');
        $mockRequest
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn(array('Content-Type' => 'application/json; charset=utf-8'));

        $controller = new \DC\Router\JsonController();
        $controller->setRequest($mockRequest);

        $deserialized = $controller->getRequestBodyAsObject();
        $this->assertEquals(23, $deserialized->id);
        $this->assertEquals('foo', $deserialized->name);
    }

    /**
     * @expectedException \DC\Router\Exceptions\UnknownContentTypeException
     */
    function testThrowsIfWrongContentType() {
        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('{"id":23,"name":"foo"}');
        $mockRequest
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn(array('Content-Type' => 'text/json'));

        $controller = new \DC\Router\JsonController();
        $controller->setRequest($mockRequest);

        $deserialized = $controller->getRequestBodyAsObject();
    }
}
 