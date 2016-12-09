<?php
namespace DC\Router\Swagger;

interface ILicense {
    /**
     * Required
     *
     * @return string
     */
    function getName();

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     *
     * @return string
     */
    function getUrl();

}