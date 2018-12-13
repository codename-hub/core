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
     * name of a log
     * @var string|null
     */
    protected $log = null;

    /**
     * Creates instance and adds the server
     * @param array $config
     * @return \codename\core\cache\memcached
     */
    public function __construct(array $config) {

      if (isset($config['env_host'])) {
        $host = getenv($config['env_host']);
      } else if(isset($config['host'])) {
        $host = $config['host'];
      } else {
        throw new \codename\core\exception('EXCEPTION_MEMCACHED_CONFIG_HOST_UNDEFINED', \codename\core\exception::$ERRORLEVEL_FATAL);
      }

      if (isset($config['env_port'])) {
        $port = getenv($config['env_port']);
      } else if(isset($config['port'])) {
        $port = $config['port'];
      } else {
        throw new \codename\core\exception('EXCEPTION_MEMCACHED_CONFIG_PORT_UNDEFINED', \codename\core\exception::$ERRORLEVEL_FATAL);
      }

      $this->memcached = new \Memcached();

      //
      // set some client options
      //
      $this->setOptions();

      $this->memcached->addServer($host, $port);

      $this->log = $config['log'] ?? null;
      $this->attach(new \codename\core\observer\cache());
      return $this;
    }

    /**
     * set options for the memcached clients
     * may be overridden/extended by inherting from this class
     */
    protected function setOptions() {
      $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL,true);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function get(string $group, string $key) {
        $data = $this->uncompress($this->memcached->get("{$group}_{$key}"));

        if($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
          return null;
        }

        $this->notify('CACHE_GET');
        if($this->log) {
          app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_GET::GETTING($group = ' . $group . ', $key= ' . $key . ')');
        }
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
    public function set(string $group, string $key, $value = null, int $timeout = null) {
        if(is_null($value)) {
          if($this->log) {
            app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::EMPTY VALUE ($group = ' . $group . ', $key= ' . $key . ')');
          }
          return;
        }

        $this->notify('CACHE_SET');
        if ($timeout == 0 || $timeout == null) {
            $timeout = 86400;
        }
        if($this->log) {
          app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_SET::SETTING ($group = ' . $group . ', $key= ' . $key . ')');
        }
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
        if($this->log) {
          app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARKEY::CLEARING ($group = ' . $group . ', $key= ' . $key . ')');
        }
        $this->clear("{$group}_{$key}");
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::clear($key)
     */
    public function clear(string $key) {
        if($this->log) {
          app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEAR::CLEARING ($key= ' . $key . ')');
        }
        $this->memcached->delete($key);
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::clearGroup($group)
     */
    public function clearGroup(string $group) {
        if($this->log) {
          app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_CLEARGROUP::CLEARING ($group = ' . $group . ')');
        }
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
