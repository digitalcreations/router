<?php

namespace DC\Router;

class BodyTag extends \phpDocumentor\Reflection\DocBlock\Tag {
    public static $name = 'body';

    public function __toString()
    {
        return (string)$this->description;
    }

    public function getVariableName() {
        return trim($this->getContent(), '$ ');
    }
}