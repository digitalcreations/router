<?php

namespace DC\Router\Swagger;

class License implements ILicense
{
    private $name;
    private $url;

    function __construct($name, $url = null)
    {
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     *
     * @return string
     */
    function getUrl()
    {
        return $this->url;
    }
}