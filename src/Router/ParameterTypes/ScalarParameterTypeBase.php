<?php

namespace DC\Router\ParameterTypes;

abstract class ScalarParameterTypeBase implements \DC\Router\IParameterType {
    private $type;
    /**
     * @var
     */
    private $regEx;

    function __construct($type, $regEx)
    {
        $this->type = $type;
        $this->regEx = $regEx;
    }

    /**
     * @return string Short string that identifies this type
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * @return string The partial regular expression to allow for this parameter
     */
    function getRegularExpression()
    {
        return $this->regEx;
    }

    /**
     * Allows you to transform the parameter value before handing it over to a route.
     *
     * This is useful for example if you have a "user" parameter which you want to always convert to a User object.
     * You could do something like if the incoming value is the user ID:
     *
     *   return User::fromId($value);
     *
     * @param $value string The incoming value
     * @return mixed Return the value as transformed a
     */
    function transformValue($value)
    {
        settype($value, $this->getType());
        return $value;
    }
}