<?php

namespace DC\Router\OutputCache;

class CacheFilter implements \DC\Router\IGlobalFilter {
    const TAG_CACHE = "cache";
    const TAG_EXCLUDE = "cache-exclude";
    const TAG_STATE = "cache-state";

    /**
     * @var \DC\Cache\ICache
     */
    private $cache;
    /**
     * @var IKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ParameterPreparer
     */
    private $parameterPreparer;

    /**
     * @param \DC\Cache\ICache $cache
     * @param $stateProviders array|\DC\Router\OutputCache\IStateProvider[]
     * @param IKeyGenerator $keyGenerator
     */
    function __construct(\DC\Cache\ICache $cache, array $stateProviders = [], IKeyGenerator $keyGenerator = null)
    {
        $this->reflector = new Reflector();

        $this->cache = $cache;

        $this->parameterPreparer = new ParameterPreparer($this->reflector, $stateProviders);
        $this->keyGenerator = isset($keyGenerator) ? $keyGenerator : new DefaultKeyGenerator();

        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(self::TAG_CACHE, '\DC\Router\OutputCache\Tag\CacheTag');
        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(self::TAG_EXCLUDE, '\DC\Router\OutputCache\Tag\CacheParameterTag');
        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(self::TAG_STATE, '\DC\Router\OutputCache\Tag\CacheParameterTag');
    }

    //region Private helpers
    /**
     * @param callable $callable
     * @param $tag
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     * @throws \ReflectionException
     */
    private function getTag($callable, $tag) {
        $reflection = $this->reflector->getReflectionFunctionForCallable($callable);
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);
        $tags = $phpdoc->getTagsByName($tag);
        return count($tags) > 0 ? $tags[0] : null;
    }

    private function keyFromRouteAndParams(\DC\Router\IRoute $route, array $params, &$expires) {
        $callable = $route->getCallable();
        /** @var \DC\Router\OutputCache\Tag\CacheTag $tag */
        $tag = $this->getTag($callable, self::TAG_CACHE);
        if ($tag) {
            $expires = $tag->getExpiry();
            $params = $this->parameterPreparer->prepareParameters($callable, $params);
            return $this->keyGenerator->fromCallableAndParams($callable, $params);
        }
    }
    //endregion

    //region ICacheFilter implementation
    /**
     * @inheritdoc
     */
    function beforeRouteExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams)
    {

    }

    /**
     * @inheritdoc
     */
    function routeExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams)
    {
        $expires = null;
        if ($key = $this->keyFromRouteAndParams($route, $rawParams, $expires)) {
            $response = $this->cache->get($key);
            if ($response instanceof \DC\Router\IResponse) {
                return $response;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function afterRouteExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams, \DC\Router\IResponse $response)
    {

    }

    /**
     * @inheritdoc
     */
    function afterRouteExecuted(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams, \DC\Router\IResponse $response)
    {
        $expires = null;
        if ($key = $this->keyFromRouteAndParams($route, $rawParams, $expires)) {
            $this->cache->set($key, $response, $expires);
        }
    }
    //endregion
}