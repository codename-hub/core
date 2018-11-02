<?php
namespace codename\core\session;
use \codename\core\app;

/**
 * Store sessions in a database model
 * @package core
 * @since 2016-02-04
 */
class database extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        // save prior to serialization
        $this->sessionData = new \codename\core\datacontainer($data['session_data']);

        // setcookie ("PHPSESSID", "", time() - 3600);
        if(session_status() === PHP_SESSION_NONE) {
          @session_start();
        }

        // print_r($_COOKIE['PHPSESSID']);

        $data['session_data'] = serialize($data['session_data']);
        $this->myModel()->save($data);

        // use identify() to fill datacontainers
        $this->identify();
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
        $sess = $this->myModel()->addFilter('session_sessionid', $_COOKIE['PHPSESSID'])->search()->getResult();
        if(count($sess) == 0) {
            return;
        }
        foreach($sess as $session) {
            $this->myModel()
              ->entryLoad($session['session_id'])
              // ->entryUnsetflag(\codename\core\model\session::FLAG_ACTIVE)
              ->entrySave();
        }
        setcookie ("PHPSESSID", "", time() - 3600);
        return;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function identify() : bool {
        $data = $this->myModel()
          ->addFilter('session_sessionid', $_COOKIE['PHPSESSID'])
          // ->withFlag(\codename\core\model\session::$FLAG_ACTIVE)
          ->search()->getResult();
        if(count($data) == 0) {
            return false;
        }
        $data = $data[0];

        $this->sessionEntry = new \codename\core\datacontainer($data);

        $sessData = is_string($data['session_data']) ? unserialize($data['session_data']) : $data['session_data'];

        if(is_array($sessData)) {
          $this->sessionData = new \codename\core\datacontainer($sessData);
        }
        return true;
    }

    /**
     * contains the underlying session model entry
     * @var \codename\core\datacontainer
     */
    protected $sessionEntry = null;

    /**
     * contains the underlying session model entry
     * @var \codename\core\datacontainer
     */
    protected $sessionData = null;

    /**
     * @inheritDoc
     */
    public function getData(string $key = '')
    {
      $value = null;
      if($this->sessionData != null) {
        $value = $this->sessionData->getData($key);
      }
      if($value == null && $this->sessionEntry != null) {
        $value = $this->sessionEntry->getData($key);
      }
      return $value;
    }

    /**
     * @inheritDoc
     */
    public function setData(string $key, $data)
    {
      // parent::setData($key, $data);
      $this->sessionData->setData($key, $data);

      if($this->sessionEntry != null) {
        // update id-based session model entry
        $this->myModel()->entryLoad($this->sessionEntry->getData('session_id'))->entryUpdate( array( 'session_data' => serialize($this->sessionData->getData()) ) )->entrySave();
      } else {
        throw new \codename\core\exception("SESSION_DATABASE_SETDATA_INVALID_SESSIONENTRY", \codename\core\exception::$ERRORLEVEL_ERROR, $data);
      }
    }

    /**
     * @todo DOCUMENTATION
     * @return \codename\core\model
     */
    protected function myModel() : \codename\core\model {
        return app::getModel('session');
    }

}
