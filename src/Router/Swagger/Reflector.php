<?php

namespace DC\Router\Swagger;

class Reflector {
    /**
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    function getReflectionFunctionForCallable($callable) {
        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
        }

        if (is_array($callable)) {
            if (is_string($callable[0])) {
                return new \ReflectionMethod($callable[0], $callable[1]);
            } else {
                $reflectionObject = new \ReflectionObject($callable[0]);
                return $reflectionObject->getMethod($callable[1]);
            }
        }

        return new \ReflectionFunction($callable);
    }
}