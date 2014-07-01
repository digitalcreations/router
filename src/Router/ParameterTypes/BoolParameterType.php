<?php

namespace DC\Router\ParameterTypes;

/**
 * @codeCoverageIgnore
 */
class BoolParameterType extends ScalarParameterTypeBase {
    function __construct()
    {
        parent::__construct('bool', '.*');
    }

    function transformValue($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!isset($value)) {
            return false;
        }
        return strtolower($value) == 'true' || strtolower($value) == '1' || $value == 1;
    }
}