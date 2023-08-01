<?php

namespace codename\core\session;

use codename\core\app;
use codename\core\datacontainer;
use codename\core\exception;
use codename\core\helper\date;
use codename\core\model;
use codename\core\session;
use DateInterval;
use DateTime;
use ReflectionException;

/**
 * Store sessions in a database model
 * @package core
 * @since 2016-02-04
 */
class database extends session implements sessionInterface
{
    /**
     * session model
     * @var model
     */
    protected model $sessionModel;
    /**
     * name of the cookie to use for session identification
     * @var string
     */
    protected string $cookieName = 'core-session';
    /**
     * lifetime of the cookie
     * used for identifying the session
     * @var string
     */
    protected string $cookieLifetime = '+1 day';
    /**
     * maximum session lifetime
     * static, cannot be prolonged
     * @var string
     */
    protected string $sessionLifetime = '12 hours';
    /**
     * contains the underlying session model entry
     * @var null|datacontainer
     */
    protected ?datacontainer $sessionEntry = null;
    /**
     * contains the underlying session model entry
     * @var null|datacontainer
     */
    protected ?datacontainer $sessionData = null;

    /**
     * {@inheritDoc}
     * @param array $data
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->sessionModel = app::getModel('session');
    }

    /**
     * updates validity for a session
     * @param string $until [description]
     * @return session        [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function setValidUntil(string $until): session
    {
        if ($this->sessionEntry != null) {
            // update id-based session model entry
            $dataset = $this->myModel()->load($this->sessionEntry->getData('session_id'));

            //
            // CHANGED 2021-05-18: drop usage of entryLoad/Update/Save
            // as it may overwrite data with current sessionEntry
            // (e.g. grace period, if driver supports it in an overridden class)
            //
            if (!empty($dataset)) {
                $this->myModel()->save([
                  $this->myModel()->getPrimaryKey() => $this->sessionEntry->getData('session_id'),
                  'session_valid_until' => $until,
                ]);
            } else {
                throw new exception("SESSION_DATABASE_SETVALIDUNTIL_SESSION_DOES_NOT_EXIST", exception::$ERRORLEVEL_ERROR);
            }
        } else {
            throw new exception("SESSION_DATABASE_SETVALIDUNTIL_INVALID_SESSIONENTRY", exception::$ERRORLEVEL_ERROR, $until);
        }
        return $this;
    }

    /**
     * @return model
     * @todo DOCUMENTATION
     */
    protected function myModel(): model
    {
        return $this->sessionModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(string $key = ''): mixed
    {
        $value = $this->sessionData?->getData($key);
        if ($value == null && $this->sessionEntry != null) {
            $value = $this->sessionEntry->getData($key);
        }
        return $value;
    }

    /**
     *
     * {@inheritDoc}
     * @param array $data
     * @return session
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data): session
    {
        // save prior to serialization
        $this->sessionData = new datacontainer($data['session_data']);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        //
        // custom cookie handling
        //
        if (!isset($_COOKIE[$this->cookieName])) {
            $sessionIdentifier = bin2hex(random_bytes(16));

            $options = [
              'expires' => strtotime($this->cookieLifetime),
              'path' => '/',
              'domain' => $_SERVER['SERVER_NAME'],
            ];

            if (!$this->handleCookie($this->cookieName, $sessionIdentifier, $options)) {
                throw new exception('COOKIE_SETTING_UGH', exception::$ERRORLEVEL_FATAL);
            }

            //
            // FAKE that the cookie existed on request.
            // just for this instance. needed.
            //
            $_COOKIE[$this->cookieName] = $sessionIdentifier;

            $data['session_sessionid'] = $sessionIdentifier;
        } else {
            $data['session_sessionid'] = $_COOKIE[$this->cookieName];
        }

        // close the PHP Session for allowing better performance
        // by non-blocking session files
        $this->closePhpSession();

        $this->myModel()->save($data);

        // use identify() to fill datacontainer
        $this->identify();
        return $this;
    }

    /**
     * [handleCookie description]
     * @param string $cookieName [description]
     * @param string $cookieValue [description]
     * @param array $options [description]
     * @return bool [success]
     */
    protected function handleCookie(string $cookieName, string $cookieValue, array $options = []): bool
    {
        return setcookie($cookieName, $cookieValue, $options);
    }

    /**
     * [closePhpSession description]
     * @return void
     */
    protected function closePhpSession(): void
    {
        // close session directly after, as we don't need it anymore.
        // this enables concurrent, non-blocking requests,
        // but we can't write to $_SESSION anymore from now on
        // which is ok, because this is the database session driver
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
    }

    /**
     * [identify description]
     * @return bool [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function identify(): bool
    {
        // close the PHP Session for allowing better performance
        // by non-blocking session files
        $this->closePhpSession();

        if (!isset($_COOKIE[$this->cookieName])) {
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
          ['field' => 'session_valid_until', 'operator' => '>=', 'value' => date::getCurrentDateTimeAsDbDate()],
          ['field' => 'session_valid_until', 'operator' => '=', 'value' => null],
        ], 'OR');

        //
        // if enabled, this defines a maximum session lifetime
        //
        if ($this->sessionLifetime) {
            // flexible date, depending on keepalive
            $model->addFilter('session_created', (new DateTime('now'))->sub(DateInterval::createFromDateString($this->sessionLifetime))->format('Y-m-d H:i:s'), '>=');
        }

        $data = $model->search()->getResult();

        if (count($data) == 0) {
            $this->destroy();
            return false;
        }
        $data = $data[0];

        $this->sessionEntry = new datacontainer($data);

        $sessData = $data['session_data'];

        if (is_array($sessData)) {
            $this->sessionData = new datacontainer($sessData);
        }
        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy(): void
    {
        // CHANGED 2021-09-24: PHP8 warning exception lead to this possible bug:
        // unset cookie and trying to destroy a session may lead to destroying NULL-session ids
        if (!($_COOKIE[$this->cookieName] ?? false)) {
            return;
        }

        $sess = $this->myModel()
          ->addFilter('session_sessionid', $_COOKIE[$this->cookieName])
          ->addFilter('session_valid', true)
          ->search()->getResult();

        if (count($sess) == 0) {
            return;
        }

        //
        // Invalidate each session entry
        //
        foreach ($sess as $session) {
            //
            // CHANGED 2021-05-18: drop usage of entryLoad/Update/Save
            // (e.g. grace period, if driver supports it in an overridden class)
            //
            $this->myModel()->save([
              $this->myModel()->getPrimaryKey() => $session['session_id'],
              'session_valid' => false,
            ]);
        }

        setcookie($this->cookieName, "", 1, '/', $_SERVER['SERVER_NAME']);
    }

    /**
     * {@inheritDoc}
     */
    public function isDefined(string $key): bool
    {
        return ($this->sessionData && $this->sessionData->isDefined($key))
          || ($this->sessionEntry && $this->sessionEntry->isDefined($key));
    }

    /**
     * {@inheritDoc}
     * @param string $key
     * @param mixed $data
     * @throws exception
     */
    public function setData(string $key, mixed $data): void
    {
        $this->sessionData->setData($key, $data);

        if ($this->sessionEntry != null) {
            // update id-based session model entry
            // CHANGED 2021-05-18: drop usage of entryLoad/Update/Save
            // (e.g. grace period, if driver supports it in an overridden class)
            //
            $this->myModel()->save([
              $this->myModel()->getPrimaryKey() => $this->sessionEntry->getData('session_id'),
              'session_data' => $this->sessionData->getData(),
            ]);
        } else {
            throw new exception("SESSION_DATABASE_SETDATA_INVALID_SESSIONENTRY", exception::$ERRORLEVEL_ERROR, $data);
        }
    }

    /**
     * {@inheritDoc}
     * @param int|string $sessionId
     * @throws ReflectionException
     * @throws exception
     */
    public function invalidate(int|string $sessionId): void
    {
        if (!empty($sessionId)) {
            $sessions = $this->myModel()
              ->addFilter('session_sessionid', $sessionId)
              ->addFilter('session_valid', true)
              ->search()->getResult();

            // invalidate each session entry
            foreach ($sessions as $session) {
                //
                // CHANGED 2021-05-18: drop usage of entryLoad/Update/Save
                // (e.g. grace period, if driver supports it in an overridden class)
                //
                $this->myModel()->save([
                  $this->myModel()->getPrimaryKey() => $session[$this->myModel()->getPrimaryKey()],
                  'session_valid' => false,
                ]);
            }
        } else {
            throw new exception('EXCEPTION_SESSION_INVALIDATE_NO_SESSIONID_PROVIDED', exception::$ERRORLEVEL_ERROR);
        }
    }
}
