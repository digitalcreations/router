<?php

namespace DC\Router\Parameters;

class BodyRouteParameter extends RouteParameterBase
{
    /**
     * @var \DC\JSON\Serializer
     */
    private $serializer;
    /**
     * @var string
     */
    private $type;

    function __construct(\DC\Router\IRoute $route, $name, $type, \DC\JSON\Serializer $serializer)
    {
        parent::__construct($route, $name, null, null);
        $this->serializer = $serializer;
        $this->type = $type;
    }

    /**
     * Return the value of a parameter for a given request.
     *
     * @param \DC\Router\IRequest $request
     * @param array $valueMap
     * @return mixed
     */
    function getValueForRequest(\DC\Router\IRequest $request, array $valueMap)
    {
        $body = $request->getBody();
        return $this->serializer->deserialize($body, $this->type, 'json');
    }

    /**
     * Get where in the request you can expect to find this parameter.
     *
     * @return string
     * @see \DC\Router\ParameterPlacement
     */
    function getPlacement()
    {
        return \DC\Router\ParameterPlacement::Body;
    }
}