<?php

namespace DC\Router\Swagger;

class Contact implements IContact {
    private $name;
    private $url;
    private $email;

    function __construct($name, $url = null, $email = null)
    {
        $this->name = $name;
        $this->url = $url;
        $this->email = $email;
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

    /**
     * @return string
     */
    function getEmail()
    {
        return $this->email;
    }
}