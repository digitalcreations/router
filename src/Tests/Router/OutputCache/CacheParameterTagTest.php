<?php

namespace DC\Tests\Router\OutputCache;

class CacheParameterTagTest extends \PHPUnit_Framework_TestCase {
    function testSetContent() {
        $tag = new \DC\Router\OutputCache\Tag\CacheParameterTag("cache-exclude", "foo");
        $this->assertEquals(['foo'], $tag->getParameters());
    }

    function testSetContentWithDollarSign() {
        $tag = new \DC\Router\OutputCache\Tag\CacheParameterTag("cache-exclude", '$foo');
        $this->assertEquals(['foo'], $tag->getParameters());
    }

    function testSetContentWithWhitespace() {
        $tag = new \DC\Router\OutputCache\Tag\CacheParameterTag("cache-exclude", ' $foo  bar');
        $this->assertEquals(['foo', "bar"], $tag->getParameters());
    }
}
 