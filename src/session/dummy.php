<?php
namespace codename\core\session;

/**
 * Store sessions in a database model
 * @package core
 * @since 2016-02-04
 */
class dummy extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::getData($key)
     */
    public function getData(string $key='') {
        return null;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::setData($key, $value)
     */
    public function setData(string $key, $value) {
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::isDefined($key)
     */
    public function isDefined(string $key) : bool {
        return false;
    }
    
    /**
     * 
     */
    public function identify() : bool {
        return false;
    }
    
}
