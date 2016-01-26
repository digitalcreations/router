<?php

namespace DC\Router;

interface IClassFactory {
    function resolve($name);
    function resolveAll($name);
} 