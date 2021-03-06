<?php

namespace DC\Tests\Router;

use DC\Router\IResponse;

class FakeController extends \DC\Router\ControllerBase {
    var $routeCalled;
    var $beforeRouteCalled;
    var $afterRouteCalled;

    /**
     * @route /foo
     */
    function route() {
        $this->routeCalled = true;
    }

    function beforeRoute(array $params)
    {
        $this->beforeRouteCalled = true;
        return parent::beforeRoute($params);
    }

    function afterRoute(array $params, IResponse $response)
    {
        $this->afterRouteCalled = true;
        parent::afterRoute($params, $response);
    }
}

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

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'));

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

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'));

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

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'));

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

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'));

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
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher,
            $mockRouteFactory,
            $this->getMock('\DC\Router\IResponseWriter'),
            $this->getMock('\DC\Router\IClassFactory'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    function testControllerBeforeAndAfterRouteCalled() {
        $request = new FakeRequest("GET", "/foo");

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(array('\DC\Tests\Router\FakeController', "route"));

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $mockClassFactory = $this->getMock('\DC\Router\IClassFactory');
        $mockClassFactory
            ->expects($this->once())
            ->method('resolve')
            ->willReturn(new FakeController());

        $router = new \DC\Router\Router($mockRouteMatcher,
            $mockRouteFactory,
            $this->getMock('\DC\Router\IResponseWriter'),
            $mockClassFactory);
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
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher,
            $mockRouteFactory,
            $this->getMock('\DC\Router\IResponseWriter'),
            $this->getMock('\DC\Router\IClassFactory'));

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
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'));

        $router->route(new FakeRequest("GET", "/foo"));
    }

    function testGlobalFilterAllHooksAreCalled() {
        $response = new \DC\Router\Response();
        $response->setContent('foo');

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use ($response) { return $response; });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $request = new FakeRequest("GET", "/foo");

        $mockFilter = $this->getMock('\DC\Router\IGlobalFilter');
        $mockFilter
            ->expects($this->once())
            ->method('beforeRouteExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]));
        $mockFilter
            ->expects($this->once())
            ->method('routeExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]));
        $mockFilter
            ->expects($this->once())
            ->method('afterRouteExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]), $this->equalTo($response));
        $mockFilter
            ->expects($this->once())
            ->method('afterRouteExecuted')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]), $this->equalTo($response));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $this->getMock('\DC\Router\IResponseWriter'), $this->getMock('\DC\Router\IClassFactory'),
            [$mockFilter]);

        $router->route($request);
    }

    function testGlobalFilterReturningResponseFromBeforeRouteExecuting() {
        $response = new \DC\Router\Response();
        $response->setContent('foo');

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use ($response) { return $response; });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $request = new FakeRequest("GET", "/foo");

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('bar');

        $mockFilter = $this->getMock('\DC\Router\IGlobalFilter');
        $mockFilter
            ->expects($this->once())
            ->method('beforeRouteExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]))
            ->willReturn($desiredResponse);

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');
        $mockResponseWriter
            ->expects($this->once())
            ->method('writeResponse')
            ->with($this->equalTo($desiredResponse));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $mockResponseWriter, $this->getMock('\DC\Router\IClassFactory'),
            [$mockFilter]);

        $router->route($request);
    }

    function testGlobalFilterReturningResponseFromRouteExecuting() {
        $response = new \DC\Router\Response();
        $response->setContent('foo');

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use ($response) { return $response; });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $request = new FakeRequest("GET", "/foo");

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('bar');

        $mockFilter = $this->getMock('\DC\Router\IGlobalFilter');
        $mockFilter
            ->expects($this->once())
            ->method('routeExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]))
            ->willReturn($desiredResponse);

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');
        $mockResponseWriter
            ->expects($this->once())
            ->method('writeResponse')
            ->with($this->equalTo($desiredResponse));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $mockResponseWriter, $this->getMock('\DC\Router\IClassFactory'),
            [$mockFilter]);

        $router->route($request);
    }

    function testGlobalFilterReturningResponseFromAfterRouteExecuting() {
        $response = new \DC\Router\Response();
        $response->setContent('foo');

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use ($response) { return $response; });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $request = new FakeRequest("GET", "/foo");

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('bar');

        $mockFilter = $this->getMock('\DC\Router\IGlobalFilter');
        $mockFilter
            ->expects($this->once())
            ->method('afterRouteExecuting')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]), $this->equalTo($response))
            ->willReturn($desiredResponse);

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');
        $mockResponseWriter
            ->expects($this->once())
            ->method('writeResponse')
            ->with($this->equalTo($desiredResponse));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $mockResponseWriter, $this->getMock('\DC\Router\IClassFactory'),
            [$mockFilter]);

        $router->route($request);
    }

    function testGlobalFilterReturningResponseFromAfterRouteExecuted() {
        $response = new \DC\Router\Response();
        $response->setContent('foo');

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->expects($this->once())
            ->method('getCallable')
            ->willReturn(function() use ($response) { return $response; });

        $mockRouteMatcher = $this->getMock('\DC\Router\IRouteMatcher');
        $mockRouteMatcher
            ->expects($this->once())
            ->method('findRoute')
            ->willReturn($mockRoute);
        $mockRouteMatcher
            ->method('extractParameters')
            ->willReturn(array());
        $mockRouteFactory = $this->getMock('\DC\Router\IRouteFactory');
        $mockRouteFactory
            ->expects($this->once())
            ->method('getRoutes')
            ->willReturn(array($mockRoute));

        $request = new FakeRequest("GET", "/foo");

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('bar');

        $mockFilter = $this->getMock('\DC\Router\IGlobalFilter');
        $mockFilter
            ->expects($this->once())
            ->method('afterRouteExecuted')
            ->with($this->equalTo($request), $this->equalTo($mockRoute), $this->equalTo([]), $this->equalTo([]), $this->equalTo($response))
            ->willReturn($desiredResponse);

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');
        $mockResponseWriter
            ->expects($this->once())
            ->method('writeResponse')
            ->with($this->equalTo($desiredResponse));

        $router = new \DC\Router\Router($mockRouteMatcher, $mockRouteFactory, $mockResponseWriter, $this->getMock('\DC\Router\IClassFactory'),
            [$mockFilter]);

        $router->route($request);
    }
}