<?php
namespace codename\core\session;

/**
 * Store sessions in the $_SESSION superglobal
 * @package core
 * @since 2016-06-21
 */
class session extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * [isSessionStarted description]
     * @return bool
     */
    protected function isSessionStarted()
    {
      if ( php_sapi_name() !== 'cli' ) {
          if ( version_compare(phpversion(), '5.4.0', '>=') ) {
              return session_status() === PHP_SESSION_ACTIVE ? true : false;
          } else {
              return session_id() === '' ? false : true;
          }
      }
      return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        if(!$this->isSessionStarted()) {
          @session_start();
          $_SESSION = $data;
        }
        // Don't forget to set some headers needed for CORS
        // in your app.
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
        // unset($_SESSION);
        session_destroy();
        setcookie ("PHPSESSID", "", time() - 3600);
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
     * [identify description]
     * @return bool [description]
     */
    public function identify() : bool {
        $data = $this->getData();
        return (is_array($data) && count($data) != 0);
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
