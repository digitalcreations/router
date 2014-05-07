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
}