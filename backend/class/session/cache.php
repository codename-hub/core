<?php
namespace codename\core\session;

use \codename\core\app;

/**
 * Storing sessions on a cache service
 * @package core
 * @since 2016-08-11
 */
class cache extends \codename\core\session {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function __construct(array $data) {
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session\sessionInterface::start()
     */
    public function start(array $data) : \codename\core\session {
        app::getCache()->set("SESSION", $this->getCachegroup(), $data);
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session\sessionInterface::destroy()
     */
    public function destroy() {
        app::getCache()->clearKey($this->getCachegroup(), "SESSION");
        return;
    }
    
    private function makeData() {
        if(is_null($this->data) || count($this->data) == 0) {
            $this->data = app::getCache()->get("SESSION", $this->getCachegroup());
        }
        return $this->data;
    }

    /**
     * Return the value of the given key. Either pass a direct name, or use a tree to navigate through the data set
     * <br /> ->get('my>config>key')
     * @param string $key
     * @return multitype
     */
    public function getData(string $key = '') {
        $this->makeData();
        
        if(strlen($key) == 0) {
            return $this->data;
        }

        if(strpos($key, '>') === false) {
            if($this->isDefined($key)) {
                return $this->data[$key];
            }
            return null;
        }

        $myConfig = $this->data;
        foreach(explode('>', $key) as $myKey) {
            if(!array_key_exists($myKey, $myConfig)) {
                return null;
            }
            $myConfig = $myConfig[$myKey];
        }

        return $myConfig;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session::setData()
     */
    public function setData(string $key, $value) {
        $data = app::getCache()->get($this->getCachegroup(), "SESSION");
        if(!is_array($data)) {
            return null;
        }
        if(strlen($key) == 0) {
            return $data;
        }
        if(!array_key_exists($key, $data)) {
            return null;
        }
        $data[$key] = $value;
        app::getCache()->set($this->getCachegroup(), "SESSION", $data);
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session::isDefined()
     */
    public function isDefined(string $key) : bool {
        $data = app::getCache()->get($this->getCachegroup(), "SESSION");
        if(!is_array($data)) {
            return false;
        }
        return array_key_exists($key, $data);
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function identify() : bool {
        $data = $this->getData();
        $this->data = $data;
        return (is_array($data) && count($data) != 0);
    }
    
    /**
     * Returns the cache group for sessions.
     * <br />Contains the application name
     * @return string
     */
    protected function getCachegroup() : string{
        return "SESSION_" . app::getApp() . "_" . $_COOKIE['PHPSESSID'];
    }

    /**
     * @inheritDoc
     */
    public function invalidate($sessionId)
    {
      throw new \LogicException('Not implemented'); // TODO
      // TODO/CHECK: app::getCache()->clearKey($this->getCachegroup(), "SESSION");
    }

}
