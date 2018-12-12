<?php
namespace codename\core\session;
use \codename\core\app;
use codename\core\exception;

/**
 * Store sessions in a database model
 * @package core
 * @since 2016-02-04
 */
class database extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * @inheritDoc
     */
    public function __construct(array $data = array())
    {
      parent::__construct($data);
      $this->sessionModel = app::getModel('session');
    }

    /**
     * session model
     * @var \codename\core\model
     */
    protected $sessionModel = null;

    /**
     * name of the cookie to use for session identification
     * @var string
     */
    protected $cookieName = 'core-session';

    /**
     * lifetime of the cookie
     * used for identifying the session
     * @var string
     */
    protected $cookieLifetime = '+1 day';

    /**
     * maximum session lifetime
     * static, cannot be prolonged
     * @var string
     */
    protected $sessionLifetime = '12 hours';

    /**
     * updates validity for a session
     * @param  string                 $until [description]
     * @return \codename\core\session        [description]
     */
    public function setValidUntil(string $until) : \codename\core\session {
      if($this->sessionEntry != null) {
        // update id-based session model entry
        $this->myModel()
          ->entryLoad($this->sessionEntry->getData('session_id'))
          ->entryUpdate([
            'session_valid_until' => $until
          ])
          ->entrySave();
      } else {
        throw new \codename\core\exception("SESSION_DATABASE_SETVALIDUNTIL_INVALID_SESSIONENTRY", \codename\core\exception::$ERRORLEVEL_ERROR, $until);
      }
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {

        // save prior to serialization
        $this->sessionData = new \codename\core\datacontainer($data['session_data']);

        if(session_status() === PHP_SESSION_NONE) {
          @session_start();
        }

        //
        // custom cookie handling
        //
        if(!isset($_COOKIE[$this->cookieName])) {
          $sessionIdentifier = bin2hex(random_bytes(16));
          if(!setcookie($this->cookieName, $sessionIdentifier, strtotime($this->cookieLifetime), '/', $_SERVER['SERVER_NAME'])) {
            throw new exception('COOKIE_SETTING_UGH', exception::$ERRORLEVEL_FATAL);
          }
          $data['session_sessionid'] = $sessionIdentifier;
        } else {
          $data['session_sessionid'] = $_COOKIE[$this->cookieName];
        }

        // close session directly after, as we don't need it anymore.
        // this enables concurrent, non-blocking requests
        // but we can't write to $_SESSION anymore from now on
        // which is ok, because this is the database session driver
        session_write_close();

        $data['session_data'] = $data['session_data'];
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
        $sess = $this->myModel()
          ->addFilter('session_sessionid', $_COOKIE[$this->cookieName])
          ->addFilter('session_valid', true)
          ->search()->getResult();

        if(count($sess) == 0) {
            return;
        }

        foreach($sess as $session) {
            $this->myModel()
              ->entryLoad($session['session_id'])
              ->entryUpdate([
                'session_valid' => false
              ])
              ->entrySave();
        }
        setcookie ($this->cookieName, "", 1);
        return;
    }

    /**
     * [identify description]
     * @return bool [description]
     */
    public function identify() : bool {

        if(!isset($_COOKIE[$this->cookieName])) {
          return false;
        }

        $model = $this->myModel();

        // filter for must-have conditions:
        // 1. session id match
        // 2. valid session (not destroyed)
        $model
          ->addFilter('session_sessionid', $_COOKIE[$this->cookieName])
          ->addFilter('session_valid', true);

        //
        // session_valid_until must be either null or above current time
        //
        $model->addFilterCollection([
          [ 'field' => 'session_valid_until', 'operator' => '>=', 'value' => \codename\core\helper\date::getCurrentDateTimeAsDbdate() ],
          [ 'field' => 'session_valid_until', 'operator' => '=', 'value' => null ]
        ], 'OR');

        //
        // if enabled, this defines a maximum session lifetime
        //
        if($this->sessionLifetime) {
           // flexible date, depending on keepalive
          $model->addFilter('session_created', (new \DateTime('now'))->sub(\DateInterval::createFromDateString($this->sessionLifetime))->format('Y-m-d H:i:s'), '>=');
        }

        $data = $model->search()->getResult();

        if(count($data) == 0) {
            return false;
        }
        $data = $data[0];

        $this->sessionEntry = new \codename\core\datacontainer($data);

        $sessData = $data['session_data'];

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
        $this->myModel()->entryLoad($this->sessionEntry->getData('session_id'))->entryUpdate( ['session_data' => $this->sessionData->getData() ] )->entrySave();
      } else {
        throw new \codename\core\exception("SESSION_DATABASE_SETDATA_INVALID_SESSIONENTRY", \codename\core\exception::$ERRORLEVEL_ERROR, $data);
      }
    }

    /**
     * @todo DOCUMENTATION
     * @return \codename\core\model
     */
    protected function myModel() : \codename\core\model {
        return $this->sessionModel;
    }

}
