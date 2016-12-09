<?php

namespace DC\Router\OutputCache;

/**
 * This interface allows you to provide a custom state for use in @cache-state tags.
 *
 * @package DC\Router\OutputCache
 */
interface IStateProvider {
    /**
     * @return string Must match the description for a PHP variable.
     */
    function getName();

    /**
     * @return string The variable to be used for caching.
     */
    function getCurrentState();
} 