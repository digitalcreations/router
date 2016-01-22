<?php

namespace DC\Router;

class DefaultParameterTypeFactory implements IParameterTypeFactory {
    /**
     * @var array|IParameterType[]
     */
    private $parameterTypes;

    /**
     * @param \DC\Router\IParameterType[] $parameterTypes
     */
    function __construct($parameterTypes)
    {
        $this->parameterTypes = array();
        foreach ($parameterTypes as $type) {
            $this->parameterTypes[$type->getType()] = $type;
        }
    }

    /**
     * @param $type string The type to find
     * @return \DC\Router\IParameterType
     */
    function getParameterFromType($type)
    {
        if (!is_array($type) && !is_object($type) && isset($this->parameterTypes[$type])) {
            return $this->parameterTypes[$type];
        }
        return null;
    }
}