<?php

namespace DC\Router\IoC;

class ModuleOptions
{
    /**
     * @var \DC\Router\Swagger\Options
     */
    private $swaggerOptions;
    private $enableOutputCache = true;

    /**
     * @param boolean $enableOutputCache
     * @return ModuleOptions
     */
    public function setEnableOutputCache(bool $enableOutputCache)
    {
        $this->enableOutputCache = $enableOutputCache;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isOutputCacheEnabled(): bool
    {
        return $this->enableOutputCache;
    }

    public function enableSwagger(\DC\Router\Swagger\Options $options) {
        $this->swaggerOptions = $options;
        return $this;
    }

    /**
     * @return \DC\Router\Swagger\Options
     */
    public function getSwaggerOptions()
    {
        return $this->swaggerOptions;
    }
}