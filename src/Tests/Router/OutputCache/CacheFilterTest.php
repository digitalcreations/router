<?php

namespace DC\Tests\Router\OutputCache;

class TestController extends \DC\Router\ControllerBase {
    /**
     * @route GET /foo/{x}/{y}
     * @cache +1 day
     * @cache-exclude $y
     */
    function foo($x, $y) {
        return (string)($x + $y);
    }
}

/**
 * @covers \DC\Router\OutputCache\CacheFilter
 */
class CacheFilterTest extends \PHPUnit_Framework_TestCase {

    function testNoTagAnonymousFunctionDoNothing() {
        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn(function() {});

        $this->assertNull($filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    function testNoTagClassCallableDoNothing() {
        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn(['\DC\Tests\Router\OutputCache\TestCallableClass', 'uncached']);

        $this->assertNull($filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    function testNoTagObjectCallableDoNothing() {
        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn([new \DC\Tests\Router\OutputCache\TestCallableClass(), 'uncached']);

        $this->assertNull($filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    function testCacheTagOnAnonymousFunctionQueriesCacheWithCorrectKey() {
        $response = new \DC\Router\Response();

        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn(/** @cache */function() {});

        $this->assertEquals($response, $filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    function testCacheTagOnClassCallableQueriesCacheWithCorrectKey() {
        $response = new \DC\Router\Response();

        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn(['\DC\Tests\Router\OutputCache\TestCallableClass', 'cached']);

        $this->assertEquals($response, $filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    function testCacheTagOnClassObjectQueriesCacheWithCorrectKey() {
        $response = new \DC\Router\Response();

        $mockCache = $this->getMock('\DC\Cache\ICache');
        $mockCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $mockRequest = $this->getMock('\DC\Router\IRequest');

        $filter = new \DC\Router\OutputCache\CacheFilter($mockCache);

        $mockRoute = $this->getMock('\DC\Router\IRoute');
        $mockRoute
            ->method('getCallable')
            ->willReturn([new \DC\Tests\Router\OutputCache\TestCallableClass(), 'cached']);

        $this->assertEquals($response, $filter->routeExecuting($mockRequest, $mockRoute, [], []));
    }

    /**
     * @param \DC\IoC\Container $container
     * @param array $controllers
     * @return \DC\Router\Router
     */
    private function routerSetup(\DC\IoC\Container $container, array $controllers) {
        // from RouterSetup
        $container->register(new \DC\Router\IoC\ClassFactory($container))->to('\DC\Router\IClassFactory');
        $container->register('\DC\Router\DefaultRouteMatcher')->to('\DC\Router\IRouteMatcher')->withContainerLifetime();
//        $container->register('\DC\Router\DefaultResponseWriter')->to('\DC\Router\IResponseWriter')->withContainerLifetime();
        $container->register('\DC\Router\DefaultParameterTypeFactory')->to('\DC\Router\IParameterTypeFactory')->withContainerLifetime();
        $container->register('\DC\Router\DefaultRequest')->to('\DC\Router\IRequest')->withContainerLifetime();

        $container->register('\DC\Router\ParameterTypes\BoolParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();
        $container->register('\DC\Router\ParameterTypes\FloatParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();
        $container->register('\DC\Router\ParameterTypes\IntParameterType')->to('\DC\Router\IParameterType')->withContainerLifetime();

        $container->register(function(\DC\Router\ClassRouteFactory $classRouteFactory) use ($controllers) {
            return new \DC\Router\DefaultRouteFactory($controllers, $classRouteFactory);
        })->to('\DC\Router\IRouteFactory')->withContainerLifetime();
        $container->register('\DC\Router\Router')->withContainerLifetime();

        // cache registration
        $container->register('\DC\Router\OutputCache\DefaultKeyGenerator')->to('\DC\Router\OutputCache\IKeyGenerator');
        $container->register('\DC\Router\OutputCache\CacheFilter')->to('\DC\Router\IGlobalFilter');

        return $container->resolve('\DC\Router\Router');
    }

    function testIntegrationWithCacheHit() {

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('4');
        $desiredResponse->setCustomHeader('Content-Type', 'text/html');

        $cache = new \DC\Cache\InMemoryCache();
        $cache->set('dcoc_DC\Tests\Router\OutputCache\TestController::foo?x=1', $desiredResponse);

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');

        $container = new \DC\IoC\Container();
        $container->register($cache)->to('\DC\Cache\ICache');
        $container->register($mockResponseWriter)->to('\DC\Router\IResponseWriter');

        $router = $this->routerSetup($container, ['\DC\Tests\Router\OutputCache\TestController']);

        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->method('getMethod')
            ->willReturn('GET');
        $mockRequest
            ->method('getPath')
            ->willReturn('/foo/1/2'); // 1+2 should equal 3, but cache says it is 4
        $mockRequest
            ->method('getRequestParameters')
            ->willReturn([]);

        $response = $router->createResponse($mockRequest);
        $this->assertEquals($desiredResponse, $response);
    }

    function testIntegrationWithCacheMiss() {

        $desiredResponse = new \DC\Router\Response();
        $desiredResponse->setContent('3');
        $desiredResponse->setCustomHeader('Content-Type', 'text/html');

        $cache = new \DC\Cache\InMemoryCache();

        $mockResponseWriter = $this->getMock('\DC\Router\IResponseWriter');

        $container = new \DC\IoC\Container();
        $container->register($cache)->to('\DC\Cache\ICache');
        $container->register($mockResponseWriter)->to('\DC\Router\IResponseWriter');

        $router = $this->routerSetup($container, ['\DC\Tests\Router\OutputCache\TestController']);

        $mockRequest = $this->getMock('\DC\Router\IRequest');
        $mockRequest
            ->method('getMethod')
            ->willReturn('GET');
        $mockRequest
            ->method('getPath')
            ->willReturn('/foo/1/2'); // this should equal 3, but cache says it is 4
        $mockRequest
            ->method('getRequestParameters')
            ->willReturn([]);

        $response = $router->createResponse($mockRequest);

        $cachedResponse = $cache->get('dcoc_DC\Tests\Router\OutputCache\TestController::foo?x=1');
        $this->assertEquals($response, $cachedResponse);
    }
}
 