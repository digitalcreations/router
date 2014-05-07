<?php

namespace DC\Router\ParameterTypes;

/**
 * @codeCoverageIgnore
 */
class IntParameterType extends ScalarParameterTypeBase {

    function __construct()
    {
        parent::__construct("int", '-?\d+');
    }
}