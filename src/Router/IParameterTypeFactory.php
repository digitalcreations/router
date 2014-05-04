<?php
namespace DC\Router;

interface IParameterTypeFactory {
    /**
     * @param $type string The type to find
     * @return \DC\Router\IParameterType
     */
    function getParameterFromType($type);
} 