<?php

namespace DC\Router\Swagger;

class ComposerPackage implements IPackage {
    private $package;

    function __construct($composerFile) {
        if (!file_exists($composerFile)) {
            throw new \DC\Router\Exceptions\FileNotFoundException($composerFile);
        }

        $this->package = json_decode(file_get_contents($composerFile), false);
    }

    function getTitle()
    {
        return $this->package->name;
    }

    function getDescription()
    {
        return $this->package->description;
    }

    function getTermsOfService()
    {
        return null; // not supported
    }

    /**
     * @return \DC\Router\Swagger\IContact
     */
    function getContact()
    {
        if (isset($this->package->authors) && is_array($this->package->authors)) {
            $author = $this->package->authors[0];
            return new Contact($author->name, $author->homepage, $author->email);
        }
        return null;
    }

    /**
     * @return \DC\Router\Swagger\ILicense
     */
    function getLicense()
    {
        if (isset($this->package->license)) {
            return new License($this->package->license);
        }
        return null;
    }

    function getVersion()
    {
        return $this->package->version;
    }
}