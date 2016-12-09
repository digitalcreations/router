<?php

namespace DC\Router\Swagger;

interface IPackage {
    function getTitle();
    function getDescription();
    function getTermsOfService();

    /**
     * @return \DC\Router\Swagger\IContact
     */
    function getContact();

    /**
     * @return \DC\Router\Swagger\ILicense
     */
    function getLicense();
    function getVersion();
}