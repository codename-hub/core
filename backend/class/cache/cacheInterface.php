<?php

namespace codename\core\cache;

/**
 * Definition for \codename\core\cache
 * @package core
 * @since 2016-04-05
 */
interface cacheInterface
{
    /**
     * Returns the value of the cache element identified by $group and $key. Returns null if the key cannot be found.
     * @param string $group
     * @param string $key
     * @return mixed
     * @access public
     */
    public function get(string $group, string $key): mixed;

    /**
     * Stores the given $value in the cache. It is identified by its $key and the $group. You can clear whole groups or only the $key
     * @param string $group
     * @param string $key
     * @param mixed $value
     * @param int|null $timeout
     * @return void
     * @access public
     */
    public function set(string $group, string $key, mixed $value = null, int $timeout = null): void;

    /**
     * Returns true if there is a cache entry identified by $group and $key.
     * @param string $group
     * @param string $key
     * @return bool
     * @access public
     */
    public function isDefined(string $group, string $key): bool;

    /**
     * Clears the cache element identified by $key in $group.
     * @param string $group
     * @param string $key
     * @return bool
     * @access public
     */
    public function clearKey(string $group, string $key): bool;


    /**
     * Clears the given $key on the cache service
     * @param string $key
     * @return bool
     */
    public function clear(string $key): bool;

    /**
     * Completely clears the given cache $group on the cache server.
     * @param string $group
     * @return bool
     * @access public
     */
    public function clearGroup(string $group): bool;

    /**
     * flushes the whole cache
     * @return bool
     */
    public function flush(): bool;
}
