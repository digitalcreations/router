<?php

namespace DC\Router;

class RouteTagTransformer extends \phpDocumentor\Reflection\DocBlock\Tag implements ITagTransformer
{
    const NAME = "route";

    private static $REGEX = '%(?:(?P<method>POST|GET|PUT|HEAD|DELETE|OPTIONS|TRACE|SEARCH|CONNECT|PROPFIND|PROPPATCH|PATCH|MKCOL|COPY|MOVE|LOCK|UNLOCK)\s+)?(?P<path>/?(?::?[a-z0-9_.()[\]{}=?&]+/?)*\$?)$%im';

    private $specification;

    public function setContent($content)
    {
        parent::setContent($content);

        if (preg_match(self::$REGEX, $content, $result)) {
            $this->specification = new RouteSpecification($result['method'], $result['path']);
        } else {
            throw new \InvalidArgumentException("Route specification not understood: " . $content);
        }

        return $this;
    }

    public function getValueForRoute()
    {
        return $this->specification;
    }
}