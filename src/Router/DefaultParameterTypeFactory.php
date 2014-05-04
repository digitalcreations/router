<?php

namespace DC\Router;

class DefaultParameterTypeFactory implements IParameterTypeFactory {
    /**
     * @var array|IParameterType[]
     */
    private $parameterTypes;

    /**
     * @param $parameterTypes \DC\Router\IParameterType[]
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
        return $this->parameterTypes[$type];
    }
}