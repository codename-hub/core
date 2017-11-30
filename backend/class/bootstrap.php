<?php
namespace codename\core;
use \codename\core\app;

/**
 * Some classes need a collection of static functions that are delivered by the bootstrap class.
 * @package codename\core
 * @since 2016-01-05
 */
class bootstrap {

    /**
     * Contains instances
     * @var array
     */
    protected static $instances = array();

    /**
     * I did not find the requested model in the appstack.
     * @var string
     */
    CONST EXCEPTION_GETMODEL_MODELNOTFOUND = 'EXCEPTION_GETMODEL_MODELNOTFOUND';

    /**
     * The current app's parent has the same name as my current app. I won't do that due to risks of infinite loops.
     * @var string
     */
    CONST EXCEPTION_GETMODEL_APPSTACKRECURSIVE = 'EXCEPTION_GETMODEL_APPSTACKRECURSIVE';

    /**
     * Returns an instance of the requested $model from the given $app or the current app
     * @param string $model
     * @param string $app
     * @return model
     */
    public static function getModel(string $model = '', string $app = '', string $vendor = '') : model {
        $model = strlen($model) == 0 ? app::getRequest()->getData('context') : $model;
        $app = strlen($app) == 0 ? app::getApp() : $app;
        $vendor = strlen($vendor) == 0 ? app::getVendor() : $vendor;

        $classname = "\\{$vendor}\\{$app}\\model\\{$model}";

        if(app::getInstance('filesystem_local')->fileAvailable(app::getHomedir($vendor, $app) . 'backend/class/model/' . $model . '.php')) {
            return new $classname(array('app' => $app));
        }

        if($app == 'core') {
            throw new \codename\core\exception(self::EXCEPTION_GETMODEL_MODELNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, array('model' => $model, 'app' => $app, 'vendor' => $vendor));
        }

        $parentapp = app::getParentapp($vendor, $app);
        $parentappdata = explode('_', $parentapp);

        $vendor = $parentappdata[0];
        $app = $parentappdata[1];

        if($parentapp == $app) {
            throw new \codename\core\exception(self::EXCEPTION_GETMODEL_APPSTACKRECURSIVE, \codename\core\exception::$ERRORLEVEL_FATAL, array('model' => $model, 'app' => $app, 'vendor' => $vendor));
        }

        return self::getModel($model, $app, $vendor);
    }

    /**
     * Returns an instance of the current request container.
     * @return request
     */
    public static function getRequest() : \codename\core\request {
        if(!array_key_exists('request', self::$instances)) {
            $classname = "\\codename\\core\\request\\" . self::getRequesttype();
            self::$instances['request'] = new $classname();
        }
        return self::$instances['request'];
    }

    /**
     * Returns the request type that is used for this request
     * @return string
     */
    protected static function getRequesttype() : string {
        if(php_sapi_name() === 'cli') {
            return 'cli';
        }
        // ?
        if(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return 'json';
        }
        return 'https';
    }

    /**
     * Returns an instance of the current response container
     * @return response
     */
    public static function getResponse() : \codename\core\response {
        if(!array_key_exists('response', self::$instances)) {
          if(array_key_exists('request', self::$instances)) {
            $classname = "\\codename\\core\\response\\" . self::getRequesttype();
            self::$instances['response'] = new $classname();
          } else {
            throw new exception(self::EXCEPTION_BOOTSTRAP_GETRESPONSE_REQUEST_INSTANCE_NOT_CREATED, exception::$ERRORLEVEL_FATAL);
          }
        }
        return self::$instances['response'];
    }

    /**
     * exception thrown, if getResponse called without existing request
     * e.g. makeRequest hasn't been called
     * @var string
     */
    public const EXCEPTION_BOOTSTRAP_GETRESPONSE_REQUEST_INSTANCE_NOT_CREATED = 'EXCEPTION_BOOTSTRAP_GETRESPONSE_REQUEST_INSTANCE_NOT_CREATED';

}
