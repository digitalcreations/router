<?php

namespace DC\Router\OutputCache;

/**
 * This implementation generates a cache key from a callable and its parameters.
 *
 * It takes some care to produce semi-readable keys; e.g. anonymous functions get a name like:
 * dcoc_anon_<filename>_<startline>_<hash>
 */
class DefaultKeyGenerator implements IKeyGenerator {
    const KEY_PREFIX = "dcoc_"; // Digital Creations Output Cache
    const KEY_PATH_SEPARATOR = "_";

    /**
     * @param callable $callable
     * @param array $params
     * @return string
     */
    function fromCallableAndParams($callable, array $params) {
        $key = self::KEY_PREFIX;
        if (is_array($callable)) {
            if (is_string($callable[0])) {
                $key .= ltrim($callable[0], '\\') . "::" . $callable[1];
            } else {
                $reflection = new \ReflectionObject($callable[0]);
                $key .= $reflection->getName()
                    . '::'
                    . $callable[1];
            }
        } else {
            $reflection = new \ReflectionFunction($callable);
            $key .= "anon_"
                . basename($reflection->getFileName(), ".php")
                . self::KEY_PATH_SEPARATOR
                . $reflection->getStartLine()
                . self::KEY_PATH_SEPARATOR
                . sha1($reflection->__toString());
        }

        if (count($params) > 0) {
            $key .= "?" . http_build_query($params);
        }

        return $key;
    }
}