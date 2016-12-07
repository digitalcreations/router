<?php
namespace DC\Router;

class ClassRouteFactory {
    /**
     * @param $className
     * @return array|\DC\Router\ClassRoute[]
     */
    function routesFromClassName($className) {
        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler(RouteTagTransformer::NAME, '\DC\Router\RouteTagTransformer');

        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods();

        $routes = [];

        foreach ($reflectionMethods as $reflectionMethod) {
            $docBlock = new \phpDocumentor\Reflection\DocBlock($reflectionMethod);
            $tags = $docBlock->getTagsByName(RouteTagTransformer::NAME);
            foreach ($tags as $tag) {
                /** @var RouteSpecification $routeValues */
                $routeValues = $tag->getValueForRoute();
                $routes[] = new ClassRoute(
                    $className,
                    $reflectionMethod->getName(),
                    $routeValues->getMethod(),
                    $routeValues->getPath(),
                    array_filter($tags, function(\phpDocumentor\Reflection\DocBlock\Tag $tag) {
                        return $tag instanceof RouteTagTransformer;
                    }));
            }
        }

        return $routes;
    }
}