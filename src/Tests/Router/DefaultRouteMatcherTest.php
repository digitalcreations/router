<?php

namespace DC\Tests\Router;

class FakeRequest implements \DC\Router\IRequest
{
    /**
     * @var
     */
    private $method;
    /**
     * @var
     */
    private $path;
    /**
     * @var
     */
    private $params;

    function __construct($method, $path, $params = array())
    {
        $this->method = $method;
        $this->path = $path;
        $this->params = $params;
    }


    /**
     * @return string HTTP method, normalized to uppercase
     */
    function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string The path part of the request.
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Return an array of parameters provided with the request. A good candidate here would be to pass a union of
     * $_GET and $_POST.
     *
     * @return string[string] string to string map of parameter names and values
     */
    function getRequestParameters()
    {
        return $this->params;
    }

    /**
     * Returns an array of all the header values.
     *
     * @return array Array of headers.
     */
    function getHeaders()
    {
        return array();
    }

    /**
     * Returns the body (POST or PUT) as a string. For empty requests (e.g. GET and DELETE), this returns null.
     *
     * @return string
     */
    function getBody()
    {
        return null;
    }
}

class DefaultRouteMatcherTest extends \PHPUnit_Framework_TestCase {
    public function testBasic() {
        $mockParameter = $this->getMock('\DC\Router\IParameterType');
        $mockParameter
            ->expects($this->any())
            ->method('getRegularExpression')
            ->willReturn('[0-9]+');

        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');
        $mockParameterFactory
            ->expects($this->any())
            ->method('getParameterFromType')
            ->willReturn($mockParameter);

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site/{name:int}/user/{id:int}/details');
        $route = $matcher->findRoute(new FakeRequest("GET", "/site/3/user/2/details"), array($mockRoute));

        $this->assertEquals($mockRoute, $route);
    }

    /**
     * @expectedException \DC\Router\Exceptions\RouteNotFoundException
     */
    public function testThrowsOnRouteNotFound() {
        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());
        $matcher->findRoute(new FakeRequest("GET", "/site/3/user"), array());
    }

    /**
     * @expectedException \DC\Router\Exceptions\MultipleRoutesFoundException
     */
    public function testThrowsOnMultipleRoutesFound() {
        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');

        $routes = array();
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site/{name}/user/{id}/details');
        $routes[] = $mockRoute;

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site/{name}/{bar}/{id}/details');
        $routes[] = $mockRoute;

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());
        $matcher->findRoute(new FakeRequest("GET", "/site/foo/user/3/details"), $routes);
    }

    public function testExtractParameters() {
        $mockParameter = $this->getMock('\DC\Router\IParameterType');
        $mockParameter
            ->expects($this->any())
            ->method('getType')
            ->willReturn('int');

        $mockParameter
            ->expects($this->any())
            ->method('getRegularExpression')
            ->willReturn('[0-9]+');
        $mockParameter
            ->expects($this->any())
            ->method('transformValue')
            ->willReturnCallback(function($x) { return (int)$x; });

        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');
        $mockParameterFactory
            ->expects($this->any())
            ->method('getParameterFromType')
            ->willReturn($mockParameter);

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site/{name}/user/{id:int}/details');
        $parameters = $matcher->extractParameters(new FakeRequest("GET", "/site/foo/user/3/details"), $mockRoute);

        $this->assertArrayHasKey('name', $parameters);
        $this->assertArrayHasKey('id', $parameters);

        $this->assertInternalType('string', $parameters['name']);
        $this->assertInternalType('int', $parameters['id']);

        $this->assertEquals(2, count($parameters));

        $this->assertEquals("foo", $parameters['name']);
        $this->assertEquals(3, $parameters['id']);
    }

    function testQueryParameters() {
        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site?user_id={id}');
        $parameters = $matcher->extractParameters(new FakeRequest("GET", "/site?user_id=3", array('user_id' => 3)), $mockRoute);
        $this->assertEquals(array('id' => 3, 'user_id' => '3'), $parameters);
    }

    function testMultipleQueryParametersInDifferentOrder() {
        $mockParameterFactory = $this->getMock('\DC\Router\IParameterTypeFactory');

        $matcher = new \DC\Router\DefaultRouteMatcher($mockParameterFactory, [], new \DC\JSON\Serializer());

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute->expects($this->any())->method('getMethod')->willReturn("GET");
        $mockRoute->expects($this->any())->method('getPath')->willReturn('/site?user_id={id}&foo={foo}');
        $parameters = $matcher->extractParameters(new FakeRequest("GET", "/site?user_id=3&foo=bar",
            array('user_id' => '3', 'foo' => 'bar')), $mockRoute);
        $this->assertEquals(array('id' => '3', 'user_id' => '3', 'foo' => 'bar'), $parameters);
    }
}
 