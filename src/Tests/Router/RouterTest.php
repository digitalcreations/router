<?php

namespace DC\Tests\Router;

class RouterTest extends \PHPUnit_Framework_TestCase {
    public function testCallsRoute() {

        $getCallableCalled = false;
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use (&$getCallableCalled) {
                $getCallableCalled = true;
                return "foo";
            });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->any())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->any())
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));

        $this->assertTrue($getCallableCalled);
    }

    public function testSortsParametersForStaticCall() {
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(array($this, 'methodCall'));

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array(
                'id' => 3,
                'site' => 'foo'
            ));
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    public static function staticCall($site, $id) {
        return (string)($site == 'foo' && $id === 3);
    }

    public function methodCall($site, $id) {
        return (string)($site == 'foo' && $id === 3);
    }

    public function testSortsParametersForMethodCall() {
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(array('\DC\Tests\Router\RouterTest', 'staticCall'));

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array(
                'site' => 'foo',
                'id' => 3
            ));
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    public function testParametersInCorrectOrder() {
        $getCallableCalled = false;
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function($id, $site) use (&$getCallableCalled) {
                $this->assertEquals("foo", $site);
                $this->assertEquals(3, $id);
                $getCallableCalled = true;
                return "foo";
            });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array(
                'site' => 'foo',
                'id' => 3
            ));

        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));

        $this->assertTrue($getCallableCalled);
    }

    /**
     * @expectedException \ReflectionException
     */
    public function testParameterOrderThrowsOnInvalidCallable() {
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(new \stdClass());

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array(
                'site' => 'foo',
                'id' => 3
            ));
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    function testControllerBeforeAndAfterRouteCalled() {
        $request = new FakeRequest("GET", "/foo");
        $mockGroup = $this->getMock('\DC\Router\IController');
        $mockGroup
            ->expects($this->once())
            ->method('beforeRoute')
            ->willReturn(true);
        $mockGroup
            ->expects($this->once())
            ->method('afterRoute')
            ->with($this->equalTo(array()), $this->isInstanceOf('\DC\Router\IResponse'))
            ->willReturn("bar");

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() {
                return "foo";
            });
        $mockRoute
            ->expects($this->once())
            ->method('getController')
            ->willReturn($mockGroup);

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));
        $router->route($request);
    }

    /**
     * @expectedException \DC\Router\Exceptions\ResponseContentIsNotStringException
     */
    function testThrowsIfResponseContentIsNotString() {
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() { return array("x" => "y"); });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    function testReturnResponse() {
        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() {
                $response = new \DC\Router\Response();
                $response->setContent('foo');
                return $response;
            });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->expects($this->once())
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'));

        $router->route(new FakeRequest("GET", "/foo"));
    }
}