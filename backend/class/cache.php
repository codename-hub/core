<?php

namespace codename\core;

use codename\core\cache\cacheInterface;

/**
 * Cache handling happens in this class
 * @package core
 * @since 2016-01-06
 */
abstract class cache extends observable implements cacheInterface
{
    /**
     * Compresses data into a JSON object, making it possible to cache objects and arrays
     * @param mixed $data
     * @return string
     */
    protected function compress(mixed $data): string
    {
        return serialize($data);
    }

    /**
     * Uncompressed the JSON data object from the cache, maybe back into an array or object
     * @param string $data
     * @return mixed
     */
    protected function uncompress(string $data): mixed
    {
        return unserialize($data);
    }
}
