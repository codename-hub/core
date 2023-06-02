<?php

namespace codename\core\cache;

use codename\core\app;
use codename\core\exception;
use codename\core\hook;
use codename\core\observer\cache;
use ReflectionException;

/**
 * Client for php-memcached
 * @package core
 * @since 2016-02-05
 */
class memcached extends \codename\core\cache
{
    /**
     * Contains the PHP memcached client class \Memcached
     * @var null|\Memcached
     */
    protected ?\Memcached $memcached = null;

    /**
     * name of a log
     * @var mixed
     */
    protected mixed $log = null;

    /**
     * Creates instance and adds the server
     * @param array $config
     * @throws exception
     */
    public function __construct(array $config)
    {
        if (isset($config['env_host'])) {
            $host = getenv($config['env_host']);
        } elseif (isset($config['host'])) {
            $host = $config['host'];
        } else {
            throw new exception('EXCEPTION_MEMCACHED_CONFIG_HOST_UNDEFINED', exception::$ERRORLEVEL_FATAL);
        }

        if (isset($config['env_port'])) {
            $port = getenv($config['env_port']);
        } elseif (isset($config['port'])) {
            $port = $config['port'];
        } else {
            throw new exception('EXCEPTION_MEMCACHED_CONFIG_PORT_UNDEFINED', exception::$ERRORLEVEL_FATAL);
        }

        $this->memcached = new \Memcached();

        //
        // set some client options
        //
        $this->setOptions();

        $this->memcached->addServer($host, $port);

        $this->log = $config['log'] ?? null;
        $this->attach(new cache());
        return $this;
    }

    /**
     * set options for the memcached clients
     * may be overridden/extended by inheriting from this class
     */
    protected function setOptions(): void
    {
        $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
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
        if ($timeout == 0 || $timeout == null) {
            $timeout = 86400;
        }
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::SETTING ($group = ' . $group . ', $key= ' . $key . ')');
        }
        $this->memcached->set("{$group}_$key", $this->compress($value), $timeout);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $group
     * @param string $key
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::isDefined($group, $key)
     */
    public function isDefined(string $group, string $key): bool
    {
        return !is_null($this->get($group, $key));
    }

    /**
     *
     * {@inheritDoc}
     * @param string $group
     * @param string $key
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function get(string $group, string $key): mixed
    {
        $data = $this->uncompress($this->memcached->get("{$group}_$key"));

        if ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
            return null;
        }

        $this->notify('CACHE_GET');
        if ($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_GET::GETTING($group = ' . $group . ', $key= ' . $key . ')');
        }
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
        if (!$this->memcached->delete($key) && $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND) {
            return false;
        } else {
            return true;
        }
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
        $keys = $this->memcached->getAllKeys();

        if (!is_array($keys)) {
            return false;
        }

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
        return $this->memcached->getAllKeys();
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
        return $this->memcached->flush();
    }
}
