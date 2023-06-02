<?php

namespace codename\core\cache;

use codename\core\app;
use codename\core\exception;
use codename\core\hook;
use codename\core\observer\cache;
use ReflectionException;

/**
 * Client for simple in-memory (array) storage
 * @package core
 * @since 2020-12-11
 */
class memory extends \codename\core\cache
{
    /**
     * The in-memory cache
     * @var null|array
     */
    protected ?array $data = null;

    /**
     * name of a log
     * @var string|null
     */
    protected ?string $log = null;

    /**
     * Creates instance and adds the server
     * @param array $config
     * @return memory
     */
    public function __construct(array $config)
    {
        $this->data = [];

        $this->attach(new cache());
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $group
     * @param string $key
     * @param mixed|null $value
     * @param int|null $timeout
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::set($group, $key, $value)
     */
    public function set(string $group, string $key, mixed $value = null, int $timeout = null): void
    {
        if (is_null($value)) {
            if ($this->log) {
                app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::EMPTY VALUE ($group = ' . $group . ', $key= ' . $key . ')');
            }
            return;
        }

        $this->notify('CACHE_SET');
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::SETTING ($group = ' . $group . ', $key= ' . $key . ')');
        }

        $this->data["{$group}_$key"] = $this->compress($value);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::isDefined($group, $key)
     */
    public function isDefined(string $group, string $key): bool
    {
        return !is_null($this->get($group, $key));
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function get(string $group, string $key): mixed
    {
        if (array_key_exists("{$group}_$key", $this->data)) {
            $data = $this->uncompress($this->data["{$group}_$key"]);
        } else {
            return null;
        }

        $this->notify('CACHE_GET');

        if (is_null($data) || (is_string($data) && strlen($data) == 0) || $data === false) {
            $this->notify('CACHE_MISS');
            app::getHook()->fire(hook::EVENT_CACHE_MISS, $key);
            return $data;
        }

        $this->notify('CACHE_HIT');
        if (is_object($data) || is_array($data)) {
            return app::object2array($data);
        }
        return $data;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $group
     * @param string $key
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::clearKey($group, $key)
     */
    public function clearKey(string $group, string $key): bool
    {
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARKEY::CLEARING ($group = ' . $group . ', $key= ' . $key . ')');
        }
        return $this->clear("{$group}_$key");
    }

    /**
     *
     * {@inheritDoc}
     * @param string $key
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::clear($key)
     */
    public function clear(string $key): bool
    {
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEAR::CLEARING ($key= ' . $key . ')');
        }

        //
        // Special handling for memcached delete
        // If the key doesn't exist, and we try to delete
        // it returns FALSE and RES_NOTFOUND
        // => which more-or-less evaluates to TRUE
        //
        unset($this->data[$key]);
        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $group
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::clearGroup($group)
     */
    public function clearGroup(string $group): bool
    {
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARGROUP::CLEARING ($group = ' . $group . ')');
        }

        //
        // NOTE: getAllKeys doesn't work with BINARY PROTOCOL
        //
        $keys = $this->getAllKeys();

        $result = true;
        foreach ($keys as $key) {
            if (str_starts_with($key, $group)) {
                $result &= $this->clear($key);
            }
        }
        return $result;
    }

    /**
     * I will return all cache keys from the cache server
     * @return array
     */
    public function getAllKeys(): array
    {
        //
        // NOTE: getAllKeys doesn't work with BINARY PROTOCOL
        //
        return array_keys($this->data);
    }

    /**
     * {@inheritDoc}
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function flush(): bool
    {
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_FLUSH::FLUSHING (ALL)');
        }

        $this->data = [];
        return true;
    }
}
