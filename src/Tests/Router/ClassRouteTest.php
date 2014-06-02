<?php

namespace DC\Tests\Router;

class ClassRouteTestController extends \DC\Router\ControllerBase {
    public $invoked = false;
    /**
     * @route GET /foo
     * @route GET /bar
     */
    public function route() {
        $this->invoked = true;
    }

    public function notRouted() { }
}

class ClassRouteTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsOnUnqualifiedClassName() {
        new \DC\Router\ClassRoute('SomeRoutable', 'route', 'GET', '/bar');
    }
}