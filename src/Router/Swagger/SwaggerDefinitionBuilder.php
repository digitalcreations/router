<?php

namespace DC\Router\Swagger;

class SwaggerDefinitionBuilder {

    private $def;

    /**
     * @var Reflector
     */
    private $reflector;
    /**
     * @var \DC\Router\IRouteMatcher
     */
    private $routeMatcher;
    /**
     * @var \DC\Router\Swagger\Options
     */
    private $options;

    function __construct(\DC\Router\IRouteMatcher $routeMatcher, \DC\Router\Swagger\Options $options = null)
    {
        $this->reflector = new Reflector();
        $this->routeMatcher = $routeMatcher;
        $this->options = $options;
    }

    /**
     * @param $routes \DC\Router\IRoute[]
     * @return array
     */
    function build(array $routes) {
        $package = $this->options->getPackage();
        $this->def = [
            "swagger" => "2.0",
            "info" => [
                "title" => $package->getTitle(),
                "description" => $package->getDescription(),
                "termsOfService" => $package->getTermsOfService(),
                "contact" => [
                    "name" => $package->getContact()->getName(),
                    "url" => $package->getContact()->getUrl(),
                    "email" => $package->getContact()->getEmail()
                ],
                "license" =>  [
                    "name" => $package->getLicense()->getName(),
                    "url" => $package->getLicense()->getUrl(),
                ],
                "version" => $package->getVersion()
            ],
            "paths" => []
        ];

        foreach ($routes as $route) {
            $this->addRouteDefinition($route);
        }
        $this->def = array_remove_null($this->def);
        return $this->def;
    }

    private function getTypeName($type) {
        return trim(str_replace("\\", "_", $type), '_');
    }

    private function isArrayType($type) {
        return strpos($type, '[]') !== false;
    }

    private function getPropertiesForType($type) {
        $properties = [];
        $reflection = new \ReflectionClass($type);
        foreach ($reflection->getProperties() as $property) {
            $phpdoc = new \phpDocumentor\Reflection\DocBlock($property);
            $varTags = $phpdoc->getTagsByName("var");
            if (count($varTags) > 0) {
                $propertyType = $varTags[0]->getType();
                $properties[$property->getName()] = $this->getTypeDefinitionOrReference($propertyType);
            }
        }

        return (object)$properties;
    }

    private function getTypeDefinition($type) {
        $scalarTypes = [
            "int" => "integer",
            "double" => "number",
            "float" => "number",
            "string" => "string",
            "bool" => "boolean",
            "\\DateTime" => "string"
        ];

        $scalarFormats = [
            "int" => "int64",
            "double" => "double",
            "float" => "float",
            "\\DateTime" => "date-time"
        ];

        if (isset($scalarTypes[$type])) {
            $def = [
                "type" => $scalarTypes[$type]
            ];
            if (isset($scalarFormats[$type])) {
                $def["format"] = $scalarFormats[$type];
            }
            return $def;
        }
        return [];
    }

    private function getTypeDefinitionOrReference($type) {
        $scalar = $this->getTypeDefinition($type);
        if ($scalar != null) {
            return $scalar;
        }
        else if ($this->isArrayType($type)) {
            $type = str_replace('[]', '', $type);
            return [
                "type" => "array",
                "items" => $this->getTypeDefinitionOrReference($type)
            ];
        }
        else {
            $def = [
                "type" => "object",
                "properties" => $this->getPropertiesForType($type)
            ];
            $this->def['definitions'][$this->getTypeName($type)] = $def;
            return ['$ref' => "#/definitions/" . $this->getTypeName($type)];
        }
    }

    private function simplifyPathForSwagger($path) {
        $findParamsRegex = '#'.\DC\Router\DefaultRouteMatcher::PARAMETER_DELIMITER_START.'([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)?'.\DC\Router\DefaultRouteMatcher::PARAMETER_DELIMITER_END.'#';
        $path = preg_replace($findParamsRegex, '{$1}', $path);
        return parse_url($path, PHP_URL_PATH);
    }


    private function addTagForController(\ReflectionClass $reflectionClass) {
        $name = strtolower(str_replace("Controller", "", $reflectionClass->getShortName()));
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflectionClass);
        $this->def['tags'][$name] = [
            "name" => $name,
            "description" => $phpdoc->getShortDescription()
        ];
        return $name;
    }

    /**
     * @param $route
     * @return array
     */
    private function addRouteDefinition(\DC\Router\IRoute $route)
    {
        $path = $this->simplifyPathForSwagger($route->getPath());
        $method = strtolower($route->getMethod());
        $callable = $route->getCallable();

        $reflection = $this->reflector->getReflectionFunctionForCallable($callable);
        if ($reflection instanceof \ReflectionMethod) {
            if (!$reflection->getDeclaringClass()->implementsInterface('\DC\Router\Swagger\ISwaggerAPI')) {
                return;
            }
        }


        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);
        if (count($phpdoc->getTagsByName(SwaggerExcludeTag::$name)) > 0) {
            return;
        }

        $routeDef = [
            "produces" => ["application/json"],
            "responses" => []
        ];

        if ($method == "post" || $method == "put") {
            $routeDef["consumes"] = ["application/json"];
        }

        if ($reflection instanceof \ReflectionMethod) {
            $reflectionClass = $reflection->getDeclaringClass();
            $routeDef["tags"] = [$this->addTagForController($reflectionClass)];
        }

        $parameters = $this->routeMatcher->getParameterInfo($route);
        $paramTags = $phpdoc->getTagsByName("param");
        $reflectionParameters = $reflection->getParameters();
        $reflectionParametersByName = [];
        foreach ($reflectionParameters as $parameter) {
            $reflectionParametersByName[$parameter->getName()] = $parameter;
        }

        foreach ($parameters as $parameter) {
            $matchingParamTags = array_filter($paramTags, function($t) use ($parameter) { return $t->getVariableName() == '$' . $parameter->getInternalName(); });
            $paramTag = reset($matchingParamTags);
            $reflectionParameter = $reflectionParametersByName[$parameter->getInternalName()];
            $required = !isset($reflectionParameter) || !$reflectionParameter->isOptional();
            $paramDef = [
                "name" => $parameter->getQueryName(),
                "in" => $parameter->getPlacement(),
                "required" => $required
            ];

            if ($paramTag != null) {
                $paramDef["description"] = $paramTag->getDescription();
                $paramDef += $this->getTypeDefinition($paramTag->getType());
            }
            else {
                $paramDef["type"] = "string";
            }
            $routeDef["parameters"][] = $paramDef;
        }

        /**
         * @var $returnTag \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag[]
         */
        $returnTag = $phpdoc->getTagsByName("return");
        if (count($returnTag) > 0) {
            $returnTypes = $returnTag[0]->getTypes();
            if (!isset($returnTypes)) {
                $returnTypes = [$returnTag[0]->getType()];
            }
            foreach ($returnTypes as $type) {
                $defRef = $this->getTypeDefinitionOrReference($type);
                $routeDef["responses"][200] = [
                    "schema" => $defRef,
                    "description" => $returnTag[0]->getDescription()
                ];
            }
        }

        /** @var $throwsTags \phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag[] */
        $throwsTags = $phpdoc->getTagsByName("throws");
        if (count($throwsTags) > 0) {
            foreach ($throwsTags as $throwsTag) {
                $type = $throwsTag->getType();
                $defRef = $this->getTypeDefinitionOrReference($type);
                $routeDef["responses"][500] = [
                    "schema" => $defRef,
                    "description" => $throwsTag->getDescription()
                ];
            }
        }

        $routeDef["summary"] = $phpdoc->getShortDescription();
        $description = $phpdoc->getLongDescription()->getContents();
        if (strlen($description) > 0) {
            $routeDef["description"] = $description;
        }

        $this->def['paths'][$path][$method] = $routeDef;
    }
}