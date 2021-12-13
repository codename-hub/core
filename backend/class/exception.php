<?php
namespace codename\core;

/**
 * We override the php's exception class to handle our exceptions by our own exception handler
 * @package core
 * @since 2016-01-05
 */
class exception extends \Exception {

    public static $ERRORLEVEL_TRIVIAL = -3;
    public static $ERRORLEVEL_DEBUG = -2;
    public static $ERRORLEVEL_NOTICE = -1;
    public static $ERRORLEVEL_NORMAL = 0;
    public static $ERRORLEVEL_WARNING = 1;
    public static $ERRORLEVEL_ERROR = 2;
    public static $ERRORLEVEL_FATAL = 3;

    /**
     * additional information
     * @var null|mixed
     */
    public $info;

    /**
     * Create an errormessage that will stop execution of this request.
     * @param string $code
     * @param int $level
     * @param mixed $info
     */
    public function __CONSTRUCT(string $code, int $level, $info = null) {

      $this->message = $this->translateExceptionCode($code);
      $this->code = $code;
      $this->info = $info;

      app::getHook()->fire($code);
    	app::getHook()->fire('EXCEPTION');

      /*
      app::getResponse()->setStatuscode(500, "Internal Server Error");

      if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
        echo "<h3>Hicks!</h3>";
        echo "<h6>{$code}</h6>";

        if(!is_null($info)) {
            echo "<h6>Information:</h6>";
            echo "<pre>";
            print_r($info);
            echo "</pre>";
        }

        echo "<h6>Stacktrace:</h6>";
        echo "<pre>";
        print_r($this->getTrace());
        echo "</pre>";
        die();
      }

      // (new \codename\core\api\loggly())->send(array('exception' => array( 'code'=>$code, 'level' => $level, 'info' => $info, 'stack' => $this->getTrace())), 1);

      app::getResponse()->pushOutput();
      return $this;
      */
    }

    /**
     * [translateExceptionCode description]
     * @param  string $code [description]
     * @return string       [description]
     */
    protected function translateExceptionCode(string $code) : string {
      return $code;
      // return app::getTranslate('exception')->translate('EXCEPTION.' . $code);
    }


}
