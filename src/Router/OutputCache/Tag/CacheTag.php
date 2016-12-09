<?php

namespace DC\Router\OutputCache\Tag;

/**
 * Empty tag that signifies that this element is cached.
 *
 * @package DC\Router\OutputCache\Tag
 */
class CacheTag extends \phpDocumentor\Reflection\DocBlock\Tag {
    const DEFAULT_INTERVAL = "+1 hour";

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        parent::setContent($content);

        $content = trim($content);
        if ($content == '') {
            $content = self::DEFAULT_INTERVAL;
        }
        try {
            $this->expires = new \DateTime($content);
        } catch(\Exception $e) {
            $this->expires = new \DateTime(self::DEFAULT_INTERVAL);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiry() {
        return $this->expires;
    }
} 