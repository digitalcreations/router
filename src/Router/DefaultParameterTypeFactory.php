<?php

namespace DC\Router;

class DefaultParameterTypeFactory implements IParameterTypeFactory {
    /**
     * @var array|IParameterType[]
     */
    private $parameterTypes;

    /**
     * @var IClassFactory
     */
    private $classFactory;

    function __construct(\DC\Router\IClassFactory $classFactory)
    {
        $this->classFactory = $classFactory;
    }

    private function getParameterTypes() {
        if ($this->parameterTypes == null) {
            $parameterTypes = $this->classFactory->resolveAll('\DC\Router\IParameterType');

            $this->parameterTypes = [];
            foreach ($parameterTypes as $type) {
                $this->parameterTypes[$type->getType()] = $type;
            }
        }
        return $this->parameterTypes;
    }

    /**
     * @param $type string The type to find
     * @return \DC\Router\IParameterType
     */
    function getParameterFromType($type)
    {
        if ($type == 'string' || $type == 'int' || $type == 'float' || $type == 'boolean') {
            return null;
        }

        $parameterTypes = $this->getParameterTypes();
        if (!is_array($type) && !is_object($type) && isset($parameterTypes[$type])) {
            return $parameterTypes[$type];
        }
        return null;
    }
}