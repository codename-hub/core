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
     * @param string $model   [name of the model]
     * @param string $app     [app or library; default is current app]
     * @param string $vendor  [vendor name; default is current vendor]
     * @return model
     */
    public static function getModel(string $model, string $app = '', string $vendor = '') : model {
        // TODO: validate $model with a modelname validator
        $app = strlen($app) == 0 ? app::getApp() : $app;
        $vendor = strlen($vendor) == 0 ? app::getVendor() : $vendor;

        // Current app may define a namespace override
        // therefore: check if requested app is this app and test for this namespace.
        if($app == app::getApp() && $vendor == app::getVendor()) {
          $namespace = app::getNamespace() ?? null;
        }

        // custom namespace from appstack config -
        // fallback to original format if not defined:
        // \vendorname\appname
        $namespace = $namespace ?? "\\{$vendor}\\{$app}";

        // check, if vendor / app is contained in current Appstack
        // otherwise, we have to explicitly do some additional work
        // to get it up and running
        $appstack = app::getAppstack();

        // determine the
        $isForeignApp = self::array_find($appstack, function($appstack) use($app, $vendor){
          return $appstack['app'] == $app && $appstack['vendor'] == $vendor;
        }) == null;

        $initConfig = [
          'vendor'  => $vendor,
          'app'     => $app
        ];

        // construct a virtual appstack
        if($isForeignApp) {
          array_splice($appstack, count($appstack)-1, 0, [[
            'vendor' => $vendor,
            'app' => $app
          ]]);
          $initConfig['appstack'] = $appstack;
        }

        // construct a FQCN to check for
        $classname = "{$namespace}\\model\\{$model}";

        // check for existance using autoloading capabilities
        if(class_exists($classname)) {
          return new $classname($initConfig);
        }

        // This is a bit tricky.
        // As we already checked for class availability
        // (with a negative result)
        // And we're already on the lowest level (core)
        // We cannot traverse the appstack any further.
        // Therefore: throw exception!
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
     * [array_find description]
     * @param  array    $xs [description]
     * @param  callable $f  [description]
     * @return mixed|null   [description]
     */
    protected static function array_find(array $xs, callable $f) {
      foreach ($xs as $x) {
        if (call_user_func($f, $x) === true)
          return $x;
      }
      return null;
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
        if(strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            return 'json';
        }
        return 'https';
    }

    /**
     * Returns an instance of the current response container
     * @return response
     */
    public static function getResponse() : \codename\core\response {
        if(!(static::$instances['response'] ?? false)) {
          if((static::$instances['request'] ?? false)) {
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
