<?php

namespace codename\core\cache;

use codename\core\cache;

/**
 * Client for devnull cache (dummy)
 * @package core
 */
class devnull extends cache
{
    /**
     * {@inheritDoc}
     */
    public function get(string $group, string $key): mixed
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $group, string $key, mixed $value = null, int $timeout = null): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isDefined(string $group, string $key): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function clearKey(string $group, string $key): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(string $key): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clearGroup(string $group): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): bool
    {
        return true;
    }
}
