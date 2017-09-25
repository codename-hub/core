<?php
namespace codename\core\cache;
use \codename\core\app;

/**
 * Client for phpmemcached
 * @package core
 * @since 2016-02-05
 */
class memcached extends \codename\core\cache {

    /**
     * Contains the PHP memcached client class \Memcached
     * @var \Memcached
     */
    protected $memcached = null;

    /**
     * Creates instance and adds the server
     * @param array $config
     * @return \codename\core\cache_memcached
     */
    public function __construct(array $config) {
        $this->memcached = new \Memcached();
        $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL,true);
        $this->memcached->addServer($config['host'], $config['port']);
        $this->attach(new \codename\core\observer\cache());
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function get(string $group, string $key) {
        $data = $this->uncompress($this->memcached->get("{$group}_{$key}"));
        $this->notify('CACHE_GET');
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_GET::GETTING($group = ' . $group . ', $key= ' . $key . ')');
        if(is_null($data) || (is_string($data) && strlen($data)==0) || $data === false) {
            $this->notify('CACHE_MISS');
            app::getHook()->fire(\codename\core\hook::EVENT_CACHE_MISS, $key);
            return $data;
        }
        $this->notify('CACHE_HIT');
        if(is_object($data) || is_array($data)) {
            return app::object2array($data);
        }
        return $data;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::set($group, $key, $value)
     */
    public function set(string $group, string $key, $value = null, int $timeout = 0) {
        if(is_null($value)) {
            app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::EMPTY VALUE ($group = ' . $group . ', $key= ' . $key . ')');
            return;
        }

        $this->notify('CACHE_SET');
        if ($timeout == 0) {
            $timeout = 86400;
        }
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::SETTING ($group = ' . $group . ', $key= ' . $key . ')');
        $this->memcached->set("{$group}_{$key}", $this->compress($value), $timeout);
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::isDefined($group, $key)
     */
    public function isDefined(string $group, string $key) : bool {
        return !is_null($this->get($group, $key));
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::clearKey($group, $key)
     */
    public function clearKey(string $group, string $key) {
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARKEY::CLEARING ($group = ' . $group . ', $key= ' . $key . ')');
        $this->clear("{$group}_{$key}");
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::clear($key)
     */
    public function clear(string $key) {
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEAR::CLEARING ($key= ' . $key . ')');
        $this->memcached->delete($key);
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::clearGroup($group)
     */
    public function clearGroup(string $group) {
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARGROUP::CLEARING ($group = ' . $group . ')');
        $keys = $this->memcached->getAllKeys();
        if(!is_array($keys)) {
            return;
        }
        foreach($keys as $key) {
            if(substr($key, 0, strlen($group)) == $group) {
                $this->clear($key);
            }
        }
        return;
    }

    /**
     * I will return all cache keys from the cacheserver
     * @return unknown
     */
    public function getAllKeys() {
    	return $this->memcached->getAllKeys();
    }

}
