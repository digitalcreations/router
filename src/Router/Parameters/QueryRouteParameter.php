<?php

namespace DC\Router\Parameters;

class QueryRouteParameter extends RouteParameterBase {
    /**
     * @inheritdoc
     */
    function getPlacement()
    {
        return \DC\Router\ParameterPlacement::Query;
    }

    /**
     * @inheritdoc
     */
    function getValueForRequest(\DC\Router\IRequest $request, array $valueMap)
    {
        if (!isset($valueMap[$this->getInternalName()])) {
            return null;
        }
        $value = $valueMap[$this->getInternalName()];
        return $this->getParameterType()->transformValue($value);
    }
}