<?php

namespace DC\Router\Swagger;

class Options {

    /**
     * @var IPackage
     */
    private $package;

    function __construct(IPackage $package)
    {
        $this->package = $package;
    }

    /**
     * @return IPackage
     */
    function getPackage() {
        return $this->package;
    }
}