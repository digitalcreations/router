<?php

namespace DC\Router\OutputCache\Tag;

class CacheParameterTag extends \phpDocumentor\Reflection\DocBlock\Tag {
    /**
     * @var string
     */
    private $parameters = [];

    const REGEX_SPLIT = '/[^a-zA-Z0-9_\x7f-\xff$]+/im';

    /**
     * From http://no2.php.net/language.variables.basics
     */
    const REGEX_VARIABLE = '/\$?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        parent::setContent($content);

        $parts = preg_split(self::REGEX_SPLIT, $content, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            if (preg_match(self::REGEX_VARIABLE, $part, $matches)) {
                $this->parameters[] = trim($matches[0], '$ ');
            }
        }

        return $this;
    }

    public function getParameters() {
        return $this->parameters;
    }
} 