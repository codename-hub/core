<?php
namespace codename\core\cache;
use \codename\core\app;

/**
 * Client for simple in-memory (array) storage
 * @package core
 * @since 2020-12-11
 */
class memory extends \codename\core\cache {

    /**
     * The in-memory cache
     * @var array
     */
    protected $data = null;

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
      $this->data = [];

      $this->attach(new \codename\core\observer\cache());
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function get(string $group, string $key) {

        $data = null;
        if(array_key_exists("{$group}_{$key}", $this->data)) {
          $data = $this->uncompress($this->data["{$group}_{$key}"]);
        } else {
          return null;
        }

        $this->notify('CACHE_GET');

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

        $this->data["{$group}_{$key}"] = $this->compress($value);
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
        return $this->clear("{$group}_{$key}");
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

        //
        // Special handling for memcached delete
        // If the key doesn't exist and we try to delete
        // it returns FALSE and RES_NOTFOUND
        // => which more-or-less evaluates to TRUE
        //
        unset($this->data[$key]);
        // if(!$this->memcached->delete($key) && $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND) {
        //   return false;
        // } else {
        //   return true;
        // }
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

        //
        // NOTE: getAllKeys doesn't work with BINARY PROTOCOL
        //
        $keys = $this->getAllKeys();

        if(!is_array($keys)) {
          // echo("Failed clearing {$group}  sys".chr(10));
          // echo($this->memcached->getLastErrorMessage().chr(10));
          // print_r($keys);
          return false; // some error
        }

        $result = true;
        foreach($keys as $key) {
            if(substr($key, 0, strlen($group)) == $group) {
                $result &= $this->clear($key);
                // if(!$result) {
                //   echo("Failed clearing {$key} ".chr(10));
                // }
            }
        }

        // if(!$result) {
        //   echo($this->memcached->getLastErrorMessage().chr(10));
        // }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
      if($this->log) {
        app::getLog($this->log)->debug('CORE_BACKEND_CLASS_CACHE_MEMCACHED_FLUSH::FLUSHING (ALL)');
      }

      $this->data = [];
      return true;
    }

    /**
     * I will return all cache keys from the cacheserver
     * @return unknown
     */
    public function getAllKeys() {
      //
      // NOTE: getAllKeys doesn't work with BINARY PROTOCOL
      //
    	return array_keys($this->data);
    }

}
