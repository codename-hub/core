<?php
namespace codename\core\response;

use \codename\core\app;

/**
 * I handle all the data for a HTTP response
 * @package core
 * @since 2016-05-31
 */
class http extends \codename\core\response {

    /**
     * Contains the status code
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Contains the status text
     * @var string
     */
    protected $statusText = 'OK';

    /**
     * @inheritDoc
     */
    protected function translateStatus()
    {
      return $this->translateStatusToHttpStatus();
      // $translate = array(
      //   self::STATUS_SUCCESS => 200,
      //   self::STATUS_INTERNAL_ERROR => 500,
      //   self::STATUS_NOTFOUND => 404,
      //   self::STATUS_FORBIDDEN => 403,
      //   self::STATUS_UNAUTHENTICATED => 401
      // );
      // return $translate[$this->status];
    }

    /**
     * [translateStatusToHttpStatus description]
     * @return int [description]
     */
    protected function translateStatusToHttpStatus() : int {
      $translate = array(
        self::STATUS_SUCCESS => 200,
        self::STATUS_INTERNAL_ERROR => 500,
        self::STATUS_NOTFOUND => 404,
        self::STATUS_FORBIDDEN => 403,
        self::STATUS_UNAUTHENTICATED => 401
      );
      return $translate[$this->status] ?? 418; // fallback: teapot
    }

    /**
     * @inheritDoc
     * simply output/echo to HTTP stream
     */
    public function pushOutput()
    {
      http_response_code($this->translateStatusToHttpStatus());
      echo $this->getOutput();
    }

    /**
     * @inheritDoc
     */
    public function setHeader(string $header)
    {
      header($header);
    }

    /**
     * Helper to set HTTP status codes
     * @param int $statusCode
     * @param string $statusText
     */
    public function setStatuscode(int $statusCode, string $statusText) : \codename\core\response {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
        return $this;
    }

    /**
     * You are requesting a resource for the front-end to load additionally.
     * <br />I'm afraid that I don't know the type of resource you requested
     * @var string
     */
    CONST EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE = 'EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE';

    /**
     * You are requesting a resource for the front-end to load additionally.
     * <br />I'm afraid that I did not find the desired resource on the file system.
     * @var string
     */
    CONST EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND = 'EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND';

    /**
     * @inheritDoc
     */
    public function __construct(array $data = array())
    {
      parent::__construct($data);
    }

    /**
     * CDN prefixes and matching rules
     */
    protected $cdnPrefixes = array();

    /**
     * sets a cdn prefix
     */
    public function setCDNResourcePrefix($prefix, $target) {
      $this->cdnPrefixes[$prefix] = $target;
    }

    /**
     * Contains data for redirecting the user after finishing the request
     * @var array | string
     */
    protected $redirect = null;

    /**
     * Redirects the user at some point to the given destination.
     * <br >Either pass a valid URL to the function (including protocol!) or pass the app/context/view/action data
     * @param string $string (URL / app)
     * @param string $context
     * @param string $view
     * @param string $action
     * @param array $params
     * @return void
     */
    public function setRedirect(string $string, string $context = null, string $view = null, string $action = null) {
        if(strpos($string, '://') != false || strpos($string, '/') === 0) {
            $this->redirect = $string;
            return;
        }
        $this->redirect = array(
            'app' => $string,
            'context' => $context,
            'view' => $view,
            'action' => $action
        );
        return;
    }

    /**
     * Sets parameters used for redirection
     * @param array
     */
    public function setRedirectArray(array $param) {
      $this->redirect = $param;
      return;
    }

    /**
     * This function performs the redirection by using a forward header ("Location: $url").
     * @return void
     * @todo make a makeUrl function for the parameters
     */
    public function doRedirect() {
        if(is_null($this->redirect)) {
            return;
        }

        if(is_string($this->redirect)) {
            $this->setHeader("Location: " . $this->redirect);
        }

        if(is_array($this->redirect)) {
            $url = '/?' . http_build_query($this->redirect);
            $this->setHeader("Location: " . $url);
        }
        return;
    }

    /**
     * I store the requirement of additional frontend resources in the response container
     * @param string $type
     * @param string $path
     * @param int $priority [last = -1, everything else: add at index]
     * @return bool
     */
    public function requireResource(string $type, string $content, int $priority = -1) : bool {
        if(!in_array($type, array('js', 'css', 'script', 'style', 'head'))) {
            throw new \codename\core\exception(self::EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE, \codename\core\exception::$ERRORLEVEL_FATAL, $type);
        }

        if(!array_key_exists($type, $this->resources)) {
          $this->resources[$type] = array();
        }

        if(($type == 'script') || ($type == 'style') || ($type == 'head')) {
          if($priority >= 0) {
            // insert at given position
            array_splice($this->resources[$type], $priority, 0, $content);
          } else {
            // add to end
            $this->resources[$type][] = $content;
          }
          return true;
        }

        if(strpos('://', $content) && !app::getInstance('filesystem_local')->fileAvailable(CORE_WEBROOT . $content)) {
            throw new \codename\core\exception(self::EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $content);
        }

        if(count($this->cdnPrefixes) > 0 && strpos('://', $content) === false && in_array($type, array('js', 'css'))) {
          foreach($this->cdnPrefixes as $prefix => $target) {
            if(strpos($content, $prefix) === 0) {
              $content = $target . ( strpos($content, '/') === 0 ? '' : '/' ) . $content;
              break;
            }
          }
        }

        if($priority >= 0) {
          // check for correct position and fix, if needed
          if(in_array($content, $this->resources[$type]) && (($pos = array_search($content, $this->resources[$type])) !== $priority)) {
            if($pos !== false) {
              // remove from old position
              unset($this->resources[$type][$pos]);
            }
          }
          // insert at given index (priority)
          array_splice($this->resources[$type], $priority, 0, $content);
        } else {
          // add to end
          if(!in_array($content, $this->resources[$type])) {
            $this->resources[$type][] = $content;
          }
        }

        return true;
    }

    /**
     * Returns an array of resources that have been requested by the backend
     * @param string $type
     * @return array
     */
    public function getResources(string $type) : array {
        if(isset($this->resources[$type])) {
            return $this->resources[$type];
        }
        return array();
    }

    /**
     * Add a JS resource to the response template
     * @param string $js
     * @return void
     */
    public function addJs(string $js) {
        $jsdo = $this->getData('jsdo');

        if(is_null($jsdo)) {
            $jsdo = array();
        }

        $jsdo[] = $js;
        $this->setData('jsdo', $jsdo);
        return;
    }

    /**
     * Will show a desktop notification on the browser if the client allowed it.
     * @see ./www/public/library/templates/shared/javascript/alpha_engine.js :: doCallback($url, callback());
     * @param string $subject
     * @param string $text
     * @param string $image
     * @param string $sound
     * @return void
     */
    public function addNotification(string $subject, string $text, string $image, string $sound) {
        $file = CORE_WEBROOT . $image;
        if(!app::getFilesystem()->fileAvailable($file)) {
            app::getLog('debug')->debug("Cannot send notification, the image {$file} is not available!");
            return;
        }

        $file = CORE_WEBROOT . $sound;
        if(!app::getFilesystem()->fileAvailable($file)) {
            app::getLog('debug')->debug("Cannot send notification, the sound {$file} is not available!");
            return;
        }

        $this->addJs("joNotify('{$subject}', '{$text}', '{$image}', '{$sound}');");
        return;
    }

    /**
     * @inheritDoc
     */
    public function displayException(\Exception $e)
    {
      $this->setStatuscode(500, "Internal Server Error");

      if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
        echo "<h3>Hicks!</h3>";
        echo "<h6>{$e->getMessage()} (Code: {$e->getCode()})</h6>";

        if($e instanceof \codename\core\exception && !is_null($e->info)) {
            echo "<h6>Information:</h6>";
            echo "<pre>";
            print_r($e->info);
            echo "</pre>";
        }

        echo "<h6>Stacktrace:</h6>";
        echo "<pre>";
        print_r($e->getTrace());
        echo "</pre>";
        die();
      }

      $this->pushOutput();
    }

}
