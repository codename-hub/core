<?php
namespace codename\core\session;

/**
 * Store sessions in the $_SESSION superglobal
 * @package core
 * @since 2016-06-21
 */
class session extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        @session_start();
        $_SESSION = $data;
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
        unset($_SESSION);
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::getData($key)
     */
    public function getData(string $key='') {
        if(strlen($key) == 0) {
            return $_SESSION;
        }
        if(!$this->isDefined($key)) {
            return null;
        }
        return $_SESSION[$key];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::setData($key, $value)
     */
    public function setData(string $key, $value) {
        $_SESSION[$key] = $value;
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::isDefined($key)
     */
    public function isDefined(string $key) : bool {
        return isset($_SESSION[$key]);
    }
    
}
