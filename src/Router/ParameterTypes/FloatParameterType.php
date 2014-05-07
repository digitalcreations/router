<?php

namespace DC\Router\ParameterTypes;

/**
 * @codeCoverageIgnore
 */
class FloatParameterType extends ScalarParameterTypeBase {

    function __construct()
    {
        parent::__construct("float", '-?(?:\d+|\d*\.\d+)');
    }
}