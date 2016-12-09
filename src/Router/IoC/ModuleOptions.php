<?php

namespace DC\Router\IoC;

class ModuleOptions
{
    private $enableOutputCache = true;

    /**
     * @param boolean $enableOutputCache
     * @return ModuleOptions
     */
    public function setEnableOutputCache(bool $enableOutputCache): ModuleOptions
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
}