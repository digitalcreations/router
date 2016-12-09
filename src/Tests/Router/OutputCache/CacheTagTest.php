<?php

namespace DC\Tests\Router\OutputCache;

class CacheTagTest extends \PHPUnit_Framework_TestCase {
    function testSetContentWithEmptyTime() {
        $tag = new \DC\Router\OutputCache\Tag\CacheTag("cache", "");
        $this->assertGreaterThan(time(), $tag->getExpiry()->getTimestamp());
    }

    function testSetContentWithInvalidTime() {
        $tag = new \DC\Router\OutputCache\Tag\CacheTag("cache", "foo");
        $this->assertGreaterThan(time(), $tag->getExpiry()->getTimestamp());
    }
}
 