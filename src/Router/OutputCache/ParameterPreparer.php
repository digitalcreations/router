<?php

namespace DC\Router\OutputCache;

class ParameterPreparer {
    /**
     * @var Reflector
     */
    private $reflector;
    /**
     * @var \DC\Router\OutputCache\IStateProvider[]
     */
    private $stateProviders = [];

    function __construct(Reflector $reflector, array $stateProviders)
    {
        $this->reflector = $reflector;
        foreach ($stateProviders as $provider) {
            $this->stateProviders[$provider->getName()] = $provider;
        }
    }

    /**
     * @param callable $callable
     * @param array $params
     * @return string[]
     * @throws \ReflectionException
     */
    function prepareParameters($callable, array $params) {
        $reflection = $this->reflector->getReflectionFunctionForCallable($callable);
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);
        /** @var \DC\Router\OutputCache\Tag\CacheParameterTag[] $excludeTags */
        $excludeTags = $phpdoc->getTagsByName(CacheFilter::TAG_EXCLUDE);
        /** @var \DC\Router\OutputCache\Tag\CacheParameterTag[] $stateTags */
        $stateTags   = $phpdoc->getTagsByName(CacheFilter::TAG_STATE);

        if (count($excludeTags) == 0 && count($stateTags) == 0) {
            return $params;
        }

        $excludeParamsKeys = isset($excludeTags[0]) ? $excludeTags[0]->getParameters() : [];
        $stateParamsKeys   = isset($stateTags  [0]) ? $stateTags  [0]->getParameters() : [];

        $excludeParams = array_flip($excludeParamsKeys);
        $stateParams   = [];
        foreach ($stateParamsKeys as $state) {
            if (!isset($this->stateProviders[$state])) {
                throw new \InvalidArgumentException($state . ' is not a registered state provider');
            }
            $stateParams[$state] = $this->stateProviders[$state]->getCurrentState();
        }

        return array_diff_key($params, $excludeParams) + $stateParams;
    }
} 