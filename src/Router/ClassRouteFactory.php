<?php
namespace DC\Router;

class ClassRouteFactory {
    private static function getPartsFromReflectionMethod(\ReflectionMethod $method) {
        $comment = $method->getDocComment();
        if (preg_match_all('%^\s*\*\s*@route\s+(?:(?P<method>POST|GET|PUT|HEAD|DELETE|OPTIONS|TRACE|SEARCH|CONNECT|PROPFIND|PROPPATCH|PATCH|MKCOL|COPY|MOVE|LOCK|UNLOCK)\s+)?(?P<route>/?(?::?[a-z0-9_.()[\]{}=?&]+/?)*\$?)\s*$%im', $comment, $result, PREG_SET_ORDER))
        {
            return array_map(function($r) use ($method) {
                return array(
                    'function' => $method->getName(),
                    'method' => empty($r['method']) ? null : strtoupper($r['method']),
                    'path' => $r['route']
                );
            }, $result);
        } else {
            return array();
        }
    }

    function routesFromClassName($className) {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods();
        $parts = array_map(array('\DC\Router\ClassRouteFactory', 'getPartsFromReflectionMethod'), $reflectionMethods);

        $routes = array();
        foreach ($parts as $partRoutes) {
            foreach ($partRoutes as $partRoute) {
                $routes[] = new ClassRoute($className, $partRoute['function'], $partRoute['method'], $partRoute['path']);
            }
        }
        return $routes;
    }
}