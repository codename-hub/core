<?php

namespace codename\core;

use codename\core\app\appInterface;
use codename\core\config\json;
use codename\core\context\customContextInterface;
use codename\core\request\cli;
use codename\core\tasks\taskschedulerInterface;
use codename\core\value\structure\appstack;
use codename\core\value\text\actionname;
use codename\core\value\text\apploader;
use codename\core\value\text\contextname;
use codename\core\value\text\methodname;
use codename\core\value\text\objectidentifier;
use codename\core\value\text\objecttype;
use codename\core\value\text\viewname;
use ErrorException;
use ReflectionClass;
use ReflectionException;
use Throwable;

class WarningException extends ErrorException
{
}

class ParseException extends ErrorException
{
}

class NoticeException extends ErrorException
{
}

class CoreErrorException extends ErrorException
{
}

class CoreWarningException extends ErrorException
{
}

class CompileErrorException extends ErrorException
{
}

class CompileWarningException extends ErrorException
{
}

class UserErrorException extends ErrorException
{
}

class UserWarningException extends ErrorException
{
}

class UserNoticeException extends ErrorException
{
}

class StrictException extends ErrorException
{
}

class RecoverableErrorException extends ErrorException
{
}

class DeprecatedException extends ErrorException
{
}

class UserDeprecatedException extends ErrorException
{
}

/**
 * This is the app base class.
 * It is ABSTRACT. You have to inherit from it.
 * It represents some core building blocks for each derived app and cannot work without deriving from it first.
 * -- may change: It handles the request through security checks, input validation and the execution of action, view and template generation
 * @package core
 * @since 2017-09-25
 */
abstract class app extends bootstrap implements appInterface
{
    /**
     * The currently requested context does not contain any views.
     * Contexts must contain at least one view.
     * @var string
     */
    public const EXCEPTION_VIEWEXISTS_CONTEXTCONTAINSNOVIEWS = 'EXCEPTION_VIEWEXISTS_CONTEXTCONTAINSNOVIEWS';

    /**
     * The currently requested view does not exist in the requested context.
     * @var string
     */
    public const EXCEPTION_MAKEREQUEST_REQUESTEDVIEWNOTINCONTEXT = 'EXCEPTION_MAKEREQUEST_REQUESTEDVIEWNOTINCONTEXT';

    /**
     * The desired application's base folder cannot be found.
     * It must exist in order to load the application's resources.
     * @var string
     */
    public const EXCEPTION_GETAPP_APPFOLDERNOTFOUND = 'EXCEPTION_GETAPP_APPFOLDERNOTFOUND';

    /**
     * The desired application config does not exist at least one context.
     * @var string
     */
    public const EXCEPTION_GETCONFIG_APPCONFIGCONTAINSNOCONTEXT = 'EXCEPTION_GETCONFIG_APPCONFIGCONTAINSNOCONTEXT';

    /**
     * The given type of the desired context is not valid
     * It either cannot be found or is an invalid configuration
     * @var string
     */
    public const EXCEPTION_GETCONFIG_APPCONFIGCONTEXTTYPEINVALID = 'EXCEPTION_GETCONFIG_APPCONFIGCONTEXTTYPEINVALID';

    /**
     * The desired application's configuration file is not valid.
     * @var string
     */
    public const EXCEPTION_GETCONFIG_APPCONFIGFILEINVALID = 'EXCEPTION_GETCONFIG_APPCONFIGFILEINVALID';

    /**
     * The current request's environment type cannot be found in the environment configuration.
     * @var string
     */
    public const EXCEPTION_GETDATA_CURRENTENVIRONMENTNOTFOUND = 'EXCEPTION_GETDATA_CURRENTENVIRONMENTNOTFOUND';

    /**
     * The requested environment type (e.g. "mail") cannot be found in the current request's environment config.
     * Maybe you missed to copy that key into your current environment.
     * @var string
     */
    public const EXCEPTION_GETDATA_REQUESTEDTYPENOTFOUND = 'EXCEPTION_GETDATA_REQUESTEDTYPENOTFOUND';

    /**
     * The current environment type configuration does not contain the desired key for the type.
     * May occur when you use multiple mail configurators.
     * @var string
     */
    public const EXCEPTION_GETDATA_REQUESTEDKEYINTYPENOTFOUND = 'EXCEPTION_GETDATA_REQUESTEDKEYINTYPENOTFOUND';

    /**
     * The desired file cannot be found in any appstack levels.
     * @var string
     */
    public const EXCEPTION_GETINHERITEDPATH_FILENOTFOUND = 'EXCEPTION_GETINHERITEDPATH_FILENOTFOUND';

    /**
     * The desired class cannot be found in any appstack level.
     * @var string
     */
    public const EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND = 'EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND';

    /**
     * The desired template cannot be found
     * @var string
     */
    public const EXCEPTION_PARSEFILE_TEMPLATENOTFOUND = 'EXCEPTION_PARSEFILE_TEMPLATENOTFOUND';

    /**
     * The desired action name is not valid
     * @var string
     */
    public const EXCEPTION_DOACTION_ACTIONNAMEISINVALID = 'EXCEPTION_DOACTION_ACTIONNAMEISINVALID';

    /**
     * The desired action is configured, but the action cannot be found in the current context
     * @var string
     */
    public const EXCEPTION_DOACTION_ACTIONNOTFOUNDINCONTEXT = 'EXCEPTION_DOACTION_ACTIONNOTFOUNDINCONTEXT';

    /**
     * The requested action's corresponding method name (action_$actionname$) cannot be found in the current context
     * @var string
     */
    public const EXCEPTION_DOACTION_REQUESTEDACTIONFUNCTIONNOTFOUND = 'EXCEPTION_DOACTION_REQUESTEDACTIONFUNCTIONNOTFOUND';

    /**
     * The requested view's name is not valid.
     * @var string
     */
    public const EXCEPTION_DOVIEW_VIEWNAMEISINVALID = 'EXCEPTION_DOVIEW_VIEWNAMEISINVALID';

    /**
     * The requested view's corresponding method name (view_$viewname$) cannot be found in the current context
     * @var string
     */
    public const EXCEPTION_DOVIEW_VIEWFUNCTIONNOTFOUNDINCONTEXT = 'EXCEPTION_DOVIEW_VIEWFUNCTIONNOTFOUNDINCONTEXT';

    /**
     * The requested view needs the user to have a specific usergroup, which he doesn't have.
     * @var string
     */
    public const EXCEPTION_DOVIEW_VIEWDISALLOWED = 'EXCEPTION_DOVIEW_VIEWDISALLOWED';

    /**
     * The requested context name is not valid
     * @var string
     */
    public const EXCEPTION_GETCONTEXT_CONTEXTNAMEISINVALID = 'EXCEPTION_GETCONTEXT_CONTEXTNAMEISINVALID';

    /**
     * The requested class file cannot be found
     * @var string
     */
    public const EXCEPTION_GETCONTEXT_REQUESTEDCLASSFILENOTFOUND = 'EXCEPTION_GETCONTEXT_REQUESTEDCLASSFILENOTFOUND';

    /**
     * The requested client could not be found
     * @var string
     */
    public const EXCEPTION_GETCLIENT_NOTFOUND = 'EXCEPTION_GETCLIENT_NOTFOUND';
    /**
     * Exception thrown if the context configuration is missing in the app.json
     * @var string
     */
    public const EXCEPTION_MAKEREQUEST_CONTEXT_CONFIGURATION_MISSING = 'EXCEPTION_MAKEREQUEST_CONTEXT_CONFIGURATION_MISSING';
    /**
     * Event/Hook that is fired when the appstack has become available
     * @var string
     */
    public const EVENT_APP_APPSTACK_AVAILABLE = 'EVENT_APP_APPSTACK_AVAILABLE';
    /**
     * Injection mode for base apps
     * (in-between core and extensions)
     * @var int
     */
    public const INJECT_APP_BASE = -1;
    /**
     * Injection mode for extension apps
     * (below main app, but above base apps)
     * @var int
     */
    public const INJECT_APP_EXTENSION = 1;
    /**
     * Injection mode for app overrides
     * (above main app!)
     * @var int
     */
    public const INJECT_APP_OVERRIDE = 0;
    /**
     * Contains the apploader for this application
     * The apploader consists of the vendor name and the app's name
     * @example codename_core
     * @var null|apploader
     */
    protected static ?apploader $apploader = null;
    /**
     * Contains the vendor of the running app. It is automatically derived from the app's namespace
     * The vendor defines the folder the resources are retrieved from
     * A vendor must consist of lowercase alphabetic characters (a-z)
     * @var null|methodname $vendor
     */
    protected static ?methodname $vendor = null;
    /**
     * Contains the name of the running app. It is automatically derived from the app's namespace
     * The app name must consist of lowercase alphabetical characters (a-z)
     * @var null|methodname $app
     */
    protected static ?methodname $app = null;
    /**
     * This contains the actual instance of the app class for singleton usage
     * Access it by app::getMyInstance()
     * @var null|app
     */
    protected static ?app $instance = null;
    /**
     * This contains an array of application names including the vendor names
     * The stack is created by searching the file ./config/parent.app in the app's directory
     * Example: array('codename_exampleapp', 'codename_someapp', 'codename_core')
     * @var null|appstack
     */
    protected static ?appstack $appstack = null;
    /**
     * This contains an instance of \codename\core\config. It is used to store the app's configuration
     * It contains the contexts, actions, views and templates of an application
     * Basically it is the outline of the app
     * @var null|config
     */
    protected static ?config $config = null;
    /**
     * This contains the environment. The environment is configured in ./config/environment.json
     * You can either use the current app's environment file or the environment file of a parent
     * Environments can be created using the $appstack property of the app class
     * @var null|config
     */
    protected static ?config $environment = null;
    /**
     * This contains an instance of \codename\core\hook. The hook class is used for event based engineering.
     * Add listeners or callbacks by using the methods in the class returned by this method.
     * @var null|hook
     */
    protected static ?hook $hook = null;
    /**
     * This is the file path that is used to load the application config.
     * It is always below the application folder.
     * @var string
     */
    protected static string $json_config = 'config/app.json';
    /**
     * This is the file path used for loading the environment config
     * It is always below the application folder
     * @var string
     */
    protected static string $json_environment = 'config/environment.json';
    /**
     * the current exit code to be sent after app's execution
     * @var null|int
     */
    protected static ?int $exitCode = 0;
    /**
     * overridden base namespace
     * @var null|string
     */
    protected static ?string $namespace = null;
    /**
     * App's home dir - null by default
     * set to override.
     * @var string|null
     */
    protected static ?string $homedir = null;
    /**
     * @var objectidentifier[]]
     */
    protected static array $dbValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $dbValueObjecttype = null;
    /**
     * @var objectidentifier[]]
     */
    protected static array $translateValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $translateValueObjecttype = null;
    /**
     * @var objectidentifier[]]
     */
    protected static array $cacheValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $cacheValueObjecttype = null;
    /**
     * @var objectidentifier[]]
     */
    protected static array $sessionValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $sessionValueObjecttype = null;
    /**
     * [protected description]
     * @var log[]
     */
    protected static array $logInstance = [];
    /**
     * @var objectidentifier[]]
     */
    protected static array $logValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $logValueObjecttype = null;
    /**
     * @var array
     */
    protected static array $validatorCacheArray = [];
    /**
     * @var objectidentifier[]]
     */
    protected static array $templateengineValueObjectidentifierArray = [];
    /**
     * @var null|objecttype
     */
    protected static ?value\text\objecttype $templateengineValueObjecttype = null;
    /**
     * array of injected or to-be-injected apps during makeAppstack
     * @var array[]
     */
    protected static array $injectedApps = [];
    /**
     * [protected description]
     * @var string[]
     */
    protected static array $injectedAppIdentifiers = [];
    /**
     * Whether to register custom shutdown handler(s)
     * @var bool
     */
    protected bool $registerShutdownHandler = true;

    /**
     * This is the entry point for an application call.
     * Either pass $app, $context, $view and $action as arguments into the constructor or let these arguments be derived from the request container
     * This method configures the application instance completely and creates all properties that are NULL by default
     * @throws CompileErrorException
     * @throws CoreErrorException
     * @throws CoreWarningException
     * @throws ParseException
     * @throws RecoverableErrorException
     * @throws StrictException
     * @throws UserErrorException
     * @throws UserWarningException
     * @throws WarningException
     * @throws ErrorException
     * @throws Throwable
     * @throws exception
     */
    public function __construct()
    {
        //
        // register shutdown handler
        // and handle fatal PHP core runtime/execution errors like
        // - maximum execution time exceeded
        // - memory limit exhausted
        // - function nesting level reached
        //
        ini_set('display_errors', 0);
        if ($this->registerShutdownHandler) {
            register_shutdown_function(function () {
                if ($error = error_get_last()) {
                    $exc = new exception('EXCEPTION_RUNTIME_SHUTDOWN', exception::$ERRORLEVEL_FATAL, $error);
                    app::getResponse()->setStatus(response::STATUS_INTERNAL_ERROR);
                    app::getResponse()->displayException($exc);
                    exit(1);
                }
            });
        }

        // Make Exceptions out of PHP Errors
        set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line) {
            //
            // https://www.php.net/manual/de/language.operators.errorcontrol.php
            // This function simply exits, if we've got a suppressed error reporting
            // (via @). This is more 'natural', as we also suppress exceptions
            // when we suppress warnings, notices, errors or else.
            // CHANGED 2021-09-24: as of PHP8, error suppression in this function has changed
            //
            if (!(error_reporting() & $err_severity)) {
                // NOTE: returning FALSE here enforces regular error handling afterwards.
                // We do not return a value to ensure core-specific behaviours stay the same.
                return;
            }
            switch ($err_severity) {
                case E_ERROR:
                    throw new ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_WARNING:
                    throw new WarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_PARSE:
                    throw new ParseException($err_msg, 0, $err_severity, $err_file, $err_line);
                    // case E_NOTICE:              throw new NoticeException           ($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_CORE_ERROR:
                    throw new CoreErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_CORE_WARNING:
                    throw new CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_COMPILE_ERROR:
                    throw new CompileErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_COMPILE_WARNING:
                    throw new CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_USER_ERROR:
                    throw new UserErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_USER_WARNING:
                    throw new UserWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                    // case E_USER_NOTICE:         throw new UserNoticeException       ($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_STRICT:
                    throw new StrictException($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_RECOVERABLE_ERROR:
                    throw new RecoverableErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                    // case E_DEPRECATED:          throw new DeprecatedException       ($err_msg, 0, $err_severity, $err_file, $err_line);
                    // case E_USER_DEPRECATED:     throw new UserDeprecatedException   ($err_msg, 0, $err_severity, $err_file, $err_line);
            }
        });

        // Core Exception Handler
        set_exception_handler(function (Throwable $t) {
            if (self::shouldThrowException()) {
                throw $t;
            } else {
                $code = is_int($t->getCode()) ? $t->getCode() : 0;
                app::getResponse()->displayException(new \Exception($t->getMessage(), $code, $t));
            }
        });

        self::getHook()->fire(hook::EVENT_APP_INITIALIZING);
        self::$instance = $this;
        self::getHook()->fire(hook::EVENT_APP_INITIALIZED);
    }

    /**
     * [shouldThrowException description]
     * @return bool
     */
    protected static function shouldThrowException(): bool
    {
        return self::getEnv() == 'dev' && extension_loaded('xdebug') && !isset($_REQUEST['XDEBUG_SESSION_STOP']) && (isset($_REQUEST['XDEBUG_SESSION_START']) || isset($_COOKIE['XDEBUG_SESSION']));
    }

    /**
     * Returns the current environment identifier
     * @return string
     */
    final public static function getEnv(): string
    {
        if (!defined("CORE_ENVIRONMENT")) {
            $env = getenv('CORE_ENVIRONMENT');
            if (!$env) {
                //
                // We have to die() here
                // as Exception Throwing+Displaying needs the environment to be defined.
                //
                echo("CORE_ENVIRONMENT not defined.");
                die();
            }
            define('CORE_ENVIRONMENT', $env);
        }
        return strtolower(CORE_ENVIRONMENT);
    }

    /**
     * Returns the app's instance of \codename\core\hook for event firing
     * @return hook
     */
    final public static function getHook(): hook
    {
        if (self::$hook === null) {
            self::$hook = hook::getInstance();
        }
        return self::$hook;
    }

    /**
     * Returns an instance of $class. It will be cached to the current $_request scope to increase performance.
     * @param string $class name of the class to load
     * @param array|null $config config to be used [WARNING: if already initialized/used, config is not being overridden!]
     * @return object
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getInstance(string $class, ?array $config = null): object
    {
        $simplename = str_replace('\\', '', $class);
        if (array_key_exists($simplename, self::$instances)) {
            return self::$instances[$simplename];
        }

        $class = self::getInheritedClass($class);

        if (!is_null($config)) {
            return self::$instances[$simplename] = new $class($config);
        }
        return self::$instances[$simplename] = new $class();
    }

    /**
     * Returns the name of the given $class name from the lowest available application's source.
     * @param string $classname
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getInheritedClass(string $classname): string
    {
        $classname = str_replace('_', '\\', $classname);

        if (is_null(self::$appstack)) {
            return "\\codename\\core\\" . $classname;
        }

        foreach (self::getAppstack() as $parentapp) {
            // CHANGED 2021-08-16: purely rely on namespace/autoload for inherited classes
            $namespace = $parentapp['namespace'] ?? ('\\' . $parentapp['vendor'] . '\\' . $parentapp['app']);
            $class = $namespace . '\\' . $classname;

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new exception(self::EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND, exception::$ERRORLEVEL_FATAL, $classname);
    }

    /**
     * Returns the appstack of the instance. Can be used to load files by their existence (not my app? -> parent app? -> parent's parent...)
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getAppstack(): array
    {
        if (self::$appstack == null) {
            self::makeCurrentAppstack();
        }
        return self::$appstack->get();
    }

    /**
     * creates and sets the appstack for the current app
     * @return array [description]
     * @throws ReflectionException
     * @throws exception
     */
    final protected static function makeCurrentAppstack(): array
    {
        $stack = self::makeAppstack(self::getVendor(), self::getApp());
        self::$appstack = new appstack($stack);
        self::getHook()->fire(app::EVENT_APP_APPSTACK_AVAILABLE);
        return $stack;
    }

    /**
     * Generates an array of application names that depend on from each other. Lower array positions are lower priorities
     * @param string $vendor [vendor]
     * @param string $app [app]
     * @return array
     * @throws exception|ReflectionException
     */
    final protected static function makeAppstack(string $vendor, string $app): array
    {
        $initialApp = [
          'vendor' => $vendor,
          'app' => $app,
        ];
        if ($vendor == static::getVendor() && $app == static::getApp()) {
            // set namespace override, if we're in the current app
            // may be null.
            $initialApp['namespace'] = static::getNamespace();
        }

        // add initial app as starting point for stack
        $stack = [$initialApp];
        $parentfile = self::getHomedir($vendor, $app) . 'config/parent.app';

        $current_vendor = '';
        $current_app = '';

        while (self::getInstance('filesystem_local')->fileAvailable($parentfile)) {
            $parentapp = app::getParentapp($current_vendor, $current_app);

            if (strlen($parentapp) == 0) {
                break;
            }

            $parentapp_data = explode('_', $parentapp);
            $current_vendor = $parentapp_data[0];
            $current_app = $parentapp_data[1];
            $stack[] = [
              'vendor' => $parentapp_data[0],
              'app' => $parentapp_data[1],
            ];

            self::getHook()->fire(hook::EVENT_APP_MAKEAPPSTACK_ADDED_APP);

            $parentfile = self::getHomedir($parentapp_data[0], $parentapp_data[1]) . 'config/parent.app';
        }

        // one more step to execute - core app itself
        $parentapp = app::getParentapp($current_vendor, $current_app);

        if (strlen($parentapp) > 0) {
            $parentapp_data = explode('_', $parentapp);

            $stack[] = [
              'vendor' => $parentapp_data[0],
              'app' => $parentapp_data[1],
            ];

            self::getHook()->fire(hook::EVENT_APP_MAKEAPPSTACK_ADDED_APP);
        }

        // we don't need to add the core framework explicitly
        // as an 'app', as it is returned by app::getParentapp
        // if there's no parent app defined

        // First, we inject app extensions
        foreach (self::getExtensions($vendor, $app) as $injectApp) {
            array_splice($stack, -1, 0, [$injectApp]);
        }

        // inject apps, if available.
        // Those are injected dynamically, e.g. in app constructor
        foreach (self::$injectedApps as $injectApp) {
            array_splice($stack, $injectApp['injection_mode'], 0, [$injectApp]);
        }

        // inject core-ui app before core app, if defined
        if (class_exists("\\codename\\core\\ui\\app")) {
            $uiApp = [
              'vendor' => 'codename',
              'app' => 'core-ui',
              'namespace' => '\\codename\\core\\ui',
            ];
            array_splice($stack, -1, 0, [$uiApp]);
        }

        return $stack;
    }

    /**
     * I return the vendor of this app
     * @return string
     * @throws exception|ReflectionException
     */
    final public static function getVendor(): string
    {
        if (is_null(self::$vendor)) {
            $appdata = explode('\\', self::getApploader()->get());
            self::$vendor = new methodname($appdata[0]);
        }
        return self::$vendor->get();
    }

    /**
     * Returns the apploader of this app.
     * @return apploader
     * @throws ReflectionException
     * @throws exception
     */
    final protected static function getApploader(): apploader
    {
        if (is_null(self::$apploader)) {
            self::$apploader = new apploader((new ReflectionClass(self::$instance))->getNamespaceName());
        }
        return self::$apploader;
    }

    /**
     * Simple returns the app's name
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getApp(): string
    {
        if (!is_null(self::$app)) {
            return self::$app->get();
        }
        $appdata = explode('\\', self::getApploader()->get());
        self::$app = new methodname($appdata[1]);
        return self::$app->get();
    }

    /**
     * returns a custom base namespace, if desired
     * (as a starting point for the current app)
     * @return string|null
     */
    final public static function getNamespace(): ?string
    {
        return static::$namespace ?? null;
    }

    /**
     * Returns the directory where the app must be stored in
     * This method relies on the constant CORE_VENDORDIR
     * @param string $vendor
     * @param string $app
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getHomedir(string $vendor = '', string $app = ''): string
    {
        if (strlen($vendor) == 0) {
            $vendor = self::getVendor();
        }
        if (strlen($app) == 0) {
            $app = self::getApp();
        }

        $dir = null;
        if (($vendor == static::getVendor()) && ($app == static::getApp()) && (static::$homedir)) {
            // NOTE: we have to rely on appstack for 'homedir' key...
            // this only takes effect, if $homedir static var is explicitly set.
            $dir = static::$homedir;
        } elseif (static::$appstack !== null) {
            //
            // traverse appstack
            //
            foreach (static::getAppstack() as $appEntry) {
                if (($appEntry['vendor'] == $vendor) && ($appEntry['app'] == $app)) {
                    $dir = $appEntry['homedir'] ?? ($appEntry['vendor'] . '/' . $appEntry['app']);
                    break;
                }
            }
        }

        if ($dir === null) {
            // DEBUG:
            // print_r([$vendor, $app, static::getAppstack()]);

            // NOTE/WARNING:
            // We _should_ enable this in the future.
            // At the moment, it breaks a lot of scenarios -
            // but it might be security-relevant,
            // as it prevents out-of-appdir file lookups
            //
            // Before, we returned a value that is fully based on VENDOR DIR,
            // and it simply was "vendor/app".
            // now, we really return NULL, just to make sure.
            // But this won't be the end of the story.
            //
            // NOTE: changed back, crashes architect.
            //
            // throw new \codename\core\exception('EXCEPTION_APP_HOMEDIR_REQUEST_OUT_OF_SCOPE', \codename\core\exception::$ERRORLEVEL_ERROR, [
            //   'vendor'  => $vendor,
            //   'app'     => $app,
            // ]);

            // Legacy style:
            // $dir = $vendor . '/' . $app;

            // Crashes Architect:
            // if($nullFallback) {
            //   return null;
            // } else {
            //   $dir = $vendor . '/' . $app;
            // }
            $dir = $vendor . '/' . $app;
        }

        //
        // Path normalization for comparison
        // for multi-platform usage
        //
        if (DIRECTORY_SEPARATOR == '\\') {
            $dirNormalized = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        } else {
            // normalize vice-versa?
            $dirNormalized = $dir;
        }
        $realpathNormalized = realpath($dir);

        if ($realpathNormalized === $dirNormalized) {
            // assume $dir is an absolute path
            // NOTE: this is a little hacky and should be improved somehow.
            return $dirNormalized . '/';
        } else {
            return CORE_VENDORDIR . $dirNormalized . '/';
        }
    }

    /**
     * Returns the name of the parent app if it was specified in the app configuration. Returns core otherwise
     * @param string $vendor
     * @param string $app
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getParentapp(string $vendor = '', string $app = ''): string
    {
        $path = self::getHomedir($vendor, $app) . 'config/parent.app';

        if (!self::getInstance('filesystem_local')->fileAvailable($path)) {
            return 'codename_core';
        }

        return trim(self::getInstance('filesystem_local')->fileRead($path));
    }

    /**
     * get extensions for a given vendor/app
     * @param string $vendor [description]
     * @param string $app [description]
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected static function getExtensions(string $vendor, string $app): array
    {
        $appJson = self::getHomedir($vendor, $app) . 'config/app.json';
        if (self::getInstance('filesystem_local')->fileAvailable($appJson)) {
            $json = new json($appJson, false, false);
            $extensions = $json->get('extensions');
            if ($extensions !== null) {
                $extensionParameters = [];
                foreach ($extensions as $ext) {
                    $class = '\\' . str_replace('_', '\\', $ext) . '\\extension';
                    if (class_exists($class) && (new ReflectionClass($class))->isSubclassOf('\\codename\\core\\extension')) {
                        $extension = new $class();
                        $extensionParameters[] = $extension->getInjectParameters();
                    } else {
                        throw new exception('CORE_APP_EXTENSION_COULD_NOT_BE_LOADED', exception::$ERRORLEVEL_FATAL, $ext);
                    }
                }
                return $extensionParameters;
            }
        }
        return [];
    }

    /**
     * set an exitcode for the application (on exiting normally)
     * @param int $exitCode [description]
     */
    public static function setExitCode(int $exitCode): void
    {
        self::$exitCode = $exitCode;
    }

    /**
     * Convert an array to an object. Updated the original (LINK) to work recursively
     * @see http://stackoverflow.com/questions/1869091/how-to-convert-an-array-to-object-in-php
     * @param mixed|object|null $object
     * @return array
     */
    public static function object2array(mixed $object): array
    {
        if ($object === null) {
            return [];
        }
        $array = [];
        foreach ($object as $key => $value) {
            if (((array)$value === $value) || is_object($value)) {
                $array[$key] = self::object2array($value);
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * returns true if the current php process is being run from a command line interface.
     * @return bool
     */
    public static function isCli(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Translates the given key. If there's no PERIOD in the key, the function will use APP.$CONTEXT_$VIEW as prefix automatically
     * @param string $key
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    public static function translate(string $key): string
    {
        if (!strpos($key, '.')) {
            $key = strtoupper("APP." . self::getRequest()->getData('context') . '_' . self::getRequest()->getData('view') . '_' . $key);
        }

        return self::getTranslate()->translate($key);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $type
     * @param string $key
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     * @see app_interface::getData, $key)
     */
    final public static function getData(string $type, string $key): mixed
    {
        $env = self::getEnv();

        // Get the value first, regardless of success.
        $value = self::getEnvironment()->get("$env>" . $type . ">" . $key);

        // If we detect something irregular, dig deeper:
        if ($value == null) {
            if (!self::getEnvironment()->exists("$env")) {
                throw new exception(self::EXCEPTION_GETDATA_CURRENTENVIRONMENTNOTFOUND, exception::$ERRORLEVEL_ERROR, $type);
            }

            if (!self::getEnvironment()->exists("$env>" . $type)) {
                throw new exception(self::EXCEPTION_GETDATA_REQUESTEDTYPENOTFOUND, exception::$ERRORLEVEL_ERROR, $type);
            }

            if (!self::getEnvironment()->exists("$env>" . $type . ">" . $key)) {
                throw new exception(self::EXCEPTION_GETDATA_REQUESTEDKEYINTYPENOTFOUND, exception::$ERRORLEVEL_ERROR, ['type' => $type, 'key' => $key]);
            }
        } else {
            return $value;
        }
        return null;
    }

    /**
     *
     * @return config
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getEnvironment(): config
    {
        if (is_null(self::$environment)) {
            self::$environment = new json(self::$json_environment, true, true);
        }
        return self::$environment;
    }

    /**
     * Returns the translator instance that is configured as $identifier
     * @param string $identifier
     * @return translate
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getTranslate(string $identifier = 'default'): translate
    {
        $object = self::getClient('translate', $identifier);
        if ($object instanceof translate) {
            return $object;
        }
        throw new exception('EXCEPTION_GETTRANSLATE_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns the (maybe cached) client that is stored as "driver" in $identifier (app.json) for the given $type.
     * @param string $type
     * @param string $identifier
     * @param bool $store
     * @return object
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getClient(string $type, string $identifier, bool $store = true): object
    {
        $simplename = $type . $identifier;

        if ($store && array_key_exists($simplename, $_REQUEST['instances'])) {
            return $_REQUEST['instances'][$simplename];
        }

        $config = self::getData($type, $identifier);

        $app = array_key_exists('app', $config) ? $config['app'] : self::getApp();
        $vendor = self::getVendor();

        if (is_array($config['driver'])) {
            $config['driver'] = $config['driver'][0];
        }

        $classname = "\\$vendor\\$app\\$type\\" . $config['driver'];

        // if not found in app, traverse appstack
        if (!class_exists($classname)) {
            $found = false;
            foreach (self::getAppstack() as $parentapp) {
                $vendor = $parentapp['vendor'];
                $app = $parentapp['app'];
                $namespace = $parentapp['namespace'] ?? "\\$vendor\\$app";
                $classname = $namespace . "\\$type\\" . $config['driver'];

                if (class_exists($classname)) {
                    $found = true;
                    break;
                }
            }

            if ($found !== true) {
                throw new exception(self::EXCEPTION_GETCLIENT_NOTFOUND, exception::$ERRORLEVEL_FATAL, [$type, $identifier]);
            }
        }

        // instance
        $instance = new $classname($config);

        // make its own name public to the client itself
        if ($instance instanceof clientInterface) {
            $instance->setClientName($simplename);
        }

        if ($store) {
            return $_REQUEST['instances'][$simplename] = $instance;
        } else {
            return $instance;
        }
    }

    /**
     * Get path of file (in APP dir OR in core dir) if it exists there - throws error if neither
     * @param string $file
     * @param array|null $useAppstack [whether to use a specific appstack, defaults to the current one]
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getInheritedPath(string $file, ?array $useAppstack = null): string
    {
        $filename = self::getHomedir() . $file;
        if (self::getInstance('filesystem_local')->fileAvailable($filename)) {
            return $filename;
        }

        if ($useAppstack == null) {
            $useAppstack = self::getAppstack();
        }

        foreach ($useAppstack as $parentapp) {
            $vendor = $parentapp['vendor'];
            $app = $parentapp['app'];
            $dir = static::getHomedir($vendor, $app);
            $filename = $dir . $file;
            if (self::getInstance('filesystem_local')->fileAvailable($filename)) {
                return $filename;
            }
        }

        throw new exception(self::EXCEPTION_GETINHERITEDPATH_FILENOTFOUND, exception::$ERRORLEVEL_FATAL, $file);
    }

    /**
     * Returns a db instance that is configured as $identifier
     * @param string $identifier
     * @param bool $store [store the database connection]
     * @return database
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getDb(string $identifier = 'default', bool $store = true): database
    {
        $object = self::getClient('database', $identifier, $store);
        if ($object instanceof database) {
            return $object;
        }
        throw new exception('EXCEPTION_GETDB_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns the session instance that is configured as $identifier
     * @param string $identifier
     * @return session
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getSession(string $identifier = 'default'): session
    {
        $object = self::getClient('session', $identifier);
        if ($object instanceof session) {
            return $object;
        }
        throw new exception('EXCEPTION_GETSESSION_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns a mailer client. Uses the client identified by 'default' when $identifier is not passed in
     * @param string $identifier
     * @return mail
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getMailer(string $identifier = 'default'): mail
    {
        $object = self::getClient('mail', $identifier, false);
        if ($object instanceof mail) {
            return $object;
        }
        throw new exception('EXCEPTION_GETMAILER_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns the bucket masked by the given $identifier
     * @param string $identifier
     * @return bucket
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getBucket(string $identifier): bucket
    {
        $object = self::getClient('bucket', $identifier);
        if ($object instanceof bucket) {
            return $object;
        }
        throw new exception('EXCEPTION_GETBUCKET_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns an instance of the requested queue
     * @param string $identifier
     * @return queue
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getQueue(string $identifier = 'default'): queue
    {
        $object = self::getClient('queue', $identifier);
        if ($object instanceof queue) {
            return $object;
        }
        throw new exception('EXCEPTION_GETQUEUE_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns the current app instance
     * @return app
     */
    final public static function getMyInstance(): app
    {
        return self::$instance;
    }

    /**
     * Creates a value object of a specific type and using the given value
     * @param string $type [description]
     * @param mixed|null $value [description]
     * @return value         [description]
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getValueobject(string $type, mixed $value): value
    {
        $classname = self::getInheritedClass('value\\' . $type);
        return new $classname($value);
    }

    /**
     * Includes the requested $file into a separate output buffer and returns the content, after parsing $data to it
     * @param string $file
     * @param object|null $data
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    final public static function parseFile(string $file, ?object $data = null): string
    {
        if (!self::getInstance('filesystem_local')->fileAvailable($file)) {
            throw new exception(self::EXCEPTION_PARSEFILE_TEMPLATENOTFOUND, exception::$ERRORLEVEL_ERROR, $file);
        }

        ob_start();
        require $file;
        return ob_get_clean();
    }

    /**
     * Injects an app, optionally with an injection mode (the place where it goes in the appstack)
     * @param array $injectApp [array/object containing the app identifiers]
     * @param int $injectionMode [defaults to INJECT_APP_BASE]
     * @throws exception
     */
    final protected static function injectApp(array $injectApp, int $injectionMode = self::INJECT_APP_BASE): void
    {
        if (isset($injectApp['vendor']) && isset($injectApp['app']) && isset($injectApp['namespace'])) {
            $identifier = $injectApp['vendor'] . '#' . $injectApp['app'] . '#' . $injectApp['namespace'];
            // Prevent double-injecting the apps
            if (!in_array($identifier, self::$injectedAppIdentifiers)) {
                $injectApp['injection_mode'] = $injectionMode;
                self::$injectedApps[] = $injectApp;
                self::$injectedAppIdentifiers[] = $identifier;
            }
        } else {
            throw new exception("EXCEPTION_APP_INJECTAPP_CANNOT_INJECT_APP", exception::$ERRORLEVEL_FATAL, $injectApp);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     * @see app_interface::run
     */
    public function run(): void
    {
        self::getHook()->fire(hook::EVENT_APP_RUN_START);
        self::getLog('debug')->debug('CORE_BACKEND_CLASS_APP_RUN::START - ' . json_encode(self::getRequest()->getData()));
        self::getLog('access')->info(json_encode(static::getRequest()->getData()));

        // Warning:
        // "Chicken or the egg" problem.
        // We have to call $this->makeRequest();
        // Before we're using app::getResponse();
        // --
        // originally, we set APP-SRV header here.
        // this has been moved to the core response class.

        try {
            $this->makeRequest();
        } catch (\Exception $e) {
            if (self::shouldThrowException()) {
                throw $e;
            } else {
                static::getResponse()->displayException($e);
            }
        }

        self::getHook()->fire(hook::EVENT_APP_RUN_MAIN);

        try {
            if (!$this->handleAccess()) {
                return;
            }

            // perform the main application lifecycle calls
            $this->mainRun();
        } catch (\Exception $e) {
            // display exception using the current response class
            // which may be either http or even CLI !
            if (self::shouldThrowException()) {
                throw $e;
            } else {
                static::getResponse()->displayException($e);
            }
        }

        self::getHook()->fire(hook::EVENT_APP_RUN_END);

        // fire exit code
        if (self::$exitCode !== null) {
            exit(self::$exitCode);
        }
    }

    /**
     * Returns a log client. Uses the client identified by 'default' when $identifier is not passed in
     * @param string $identifier
     * @return log
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getLog(string $identifier = 'default'): log
    {
        $object = self::getSingletonClient('log', $identifier);
        if ($object instanceof log) {
            return $object;
        }
        throw new exception('EXCEPTION_GETLOG_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Returns the (maybe cached) client that is stored as "driver" in $identifier (app.json) for the given $type.
     * @param string $type
     * @param string $identifier
     * @param bool $store [whether to try to retrieve instance, if already initialized/cached]
     * @return object
     * @throws ReflectionException
     * @throws exception
     */
    final protected static function getSingletonClient(string $type, string $identifier, bool $store = true): object
    {
        // make simplename for storing instance
        $simplename = $type . $identifier;

        // check if already instanced
        if ($store && array_key_exists($simplename, $_REQUEST['instances'])) {
            return $_REQUEST['instances'][$simplename];
        }

        $config = self::getData($type, $identifier);

        // Load client information

        // Maybe overwrite data
        $app = self::getApp();
        $vendor = self::getVendor();

        if (array_key_exists('app', $config)) {
            $app = $config['app'];
        }

        // Check classpath and name in the current app
        if (is_array($config['driver'])) {
            $config['driver'] = $config['driver'][0];
        }

        $classpath = self::getHomedir($vendor, $app) . '/backend/class/' . $type . '/' . $config['driver'] . '.php';
        $classname = "\\$vendor\\$app\\$type\\" . $config['driver'];

        // if not found in app, use the core app
        if (!self::getInstance('filesystem_local')->fileAvailable($classpath)) {
            $app = 'core';
            $vendor = 'codename';
            $classname = "\\$vendor\\$app\\$type\\" . $config['driver'];
        }

        // instance
        $_REQUEST['instances'][$simplename] = call_user_func($classname . '::getInstance', $config);
        return $_REQUEST['instances'][$simplename];
    }

    /**
     * Sets the request arguments
     * @throws exception|ReflectionException
     */
    protected function makeRequest(): void
    {
        self::getRequest()->setData('context', self::getRequest()->isDefined('context') ? self::getRequest()->getData('context') : self::getConfig()->get('defaultcontext'));
        self::getRequest()->setData('view', self::getRequest()->isDefined('view') ? self::getRequest()->getData('view') : self::getConfig()->get('context>' . self::getRequest()->getData('context') . '>defaultview'));
        self::getRequest()->setData('action', self::getRequest()->isDefined('action') ? self::getRequest()->getData('action') : null);

        if (self::getConfig()->get('context>' . self::getRequest()->getData('context')) == null) {
            throw new exception(self::EXCEPTION_MAKEREQUEST_CONTEXT_CONFIGURATION_MISSING, exception::$ERRORLEVEL_ERROR, self::getRequest()->getData('context'));
        }

        if (!self::getConfig()->get('context>' . self::getRequest()->getData('context') . '>custom')) {
            if (!$this->viewExists(new contextname(self::getRequest()->getData('context')), new viewname(self::getRequest()->getData('view')))) {
                throw new exception(self::EXCEPTION_MAKEREQUEST_REQUESTEDVIEWNOTINCONTEXT, exception::$ERRORLEVEL_ERROR, self::getRequest()->getData('view'));
            }
        }

        if (!static::getRequest()->isDefined('template')) {
            if (self::getConfig()->exists("context>" . self::getRequest()->getData('context') . ">view>" . self::getRequest()->getData('view') . ">template")) {
                // view-level template config
                static::getRequest()->setData('template', self::getConfig()->get("context>" . self::getRequest()->getData('context') . ">view>" . self::getRequest()->getData('view') . ">template"));
            } elseif (self::getConfig()->exists("context>" . self::getRequest()->getData('context') . ">template")) {
                // context-level template config
                static::getRequest()->setData('template', self::getConfig()->get("context>" . self::getRequest()->getData('context') . ">template"));
            } else {
                // app-level template config
                static::getRequest()->setData('template', self::getConfig()->get("defaulttemplate"));
            }
        }
    }

    /**
     * Returns the configuration object of the application
     * @return config
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getConfig(): config
    {
        if (is_null(self::$config)) {
            // Pre-construct cachegroup
            $cacheGroup = 'APPCONFIG';
            $cacheKey = self::getVendor() . '_' . self::getApp();

            if ($finalConfig = self::getCache()->get($cacheGroup, $cacheKey)) {
                self::$config = new config($finalConfig);
                return self::$config;
            }

            $config = (new json(self::$json_config))->get();

            if (!array_key_exists('context', $config)) {
                throw new exception(self::EXCEPTION_GETCONFIG_APPCONFIGCONTAINSNOCONTEXT, exception::$ERRORLEVEL_FATAL, self::$json_config);
            }

            // Testing: Adding the default (install) context
            // TODO: Filepath-beautify
            // Using appstack=true !
            $default = (new json("/config/app.json", true, true))->get();
            $config = utils::array_merge_recursive_ex($config, $default);


            foreach ($config['context'] as $key => $value) {
                if (!array_key_exists('type', $value)) {
                    continue;
                }
                $contexttype = (new json('config/context/' . $config['context'][$key]['type'] . '.json', true))->get();

                if (count($errors = static::getValidator('structure_config_context')->reset()->validate($contexttype)) > 0) {
                    throw new exception(self::EXCEPTION_GETCONFIG_APPCONFIGCONTEXTTYPEINVALID, exception::$ERRORLEVEL_FATAL, $errors);
                }

                $config['context'][$key] = utils::array_merge_recursive_ex($config['context'][$key], $contexttype);

                if (is_array($config['context'][$key]['defaultview']) && count($config['context'][$key]['defaultview']) > 1) {
                    $config['context'][$key]['defaultview'] = $config['context'][$key]['defaultview'][0];
                }
            }

            if (count($errors = static::getValidator('structure_config_app')->reset()->validate($config)) > 0) {
                throw new exception(self::EXCEPTION_GETCONFIG_APPCONFIGFILEINVALID, exception::$ERRORLEVEL_FATAL, $errors);
            }

            self::$config = new config($config);

            self::getCache()->set($cacheGroup, $cacheKey, self::$config->get());
        }
        return self::$config;
    }

    /**
     * Returns the cache instance that is configured as $identifier
     * @param string $identifier
     * @return cache
     * @throws exception|ReflectionException
     */
    final public static function getCache(string $identifier = 'default'): cache
    {
        $object = self::getClient('cache', $identifier);
        if ($object instanceof cache) {
            return $object;
        }
        throw new exception('EXCEPTION_GETCACHE_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Loads an instance of the given validator type and returns it.
     * @param string $type Type of the validator
     * @return validator
     * @throws ReflectionException
     * @throws exception
     * @todo validate if datatype exists
     */
    final public static function getValidator(string $type): validator
    {
        if (!array_key_exists($type, self::$validatorCacheArray)) {
            $classname = self::getInheritedClass('validator\\' . $type);
            self::$validatorCacheArray[$type] = new $classname();
        }
        return self::$validatorCacheArray[$type];
    }

    /**
     *
     * {@inheritDoc}
     * @param contextname $context
     * @param viewname $view
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @todo clearly context related. move to the context
     * @see app_interface::viewExists, $view)
     */
    public function viewExists(contextname $context, viewname $view): bool
    {
        if (!$this->contextExists($context)) {
            return false;
        }

        if (!self::getConfig()->exists("context>" . $context->get() . ">view")) {
            throw new exception(self::EXCEPTION_VIEWEXISTS_CONTEXTCONTAINSNOVIEWS, exception::$ERRORLEVEL_FATAL, $context);
        }

        return self::getConfig()->exists("context>" . $context->get() . ">view>" . $view->get());
    }

    /**
     *
     * {@inheritDoc}
     * @param contextname $context
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see app_interface::contextExists
     */
    public function contextExists(contextname $context): bool
    {
        return self::getConfig()->exists("context>" . $context->get());
    }

    /**
     * [handleAccess description]
     * @return bool [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function handleAccess(): bool
    {
        if ($this->getContext() instanceof customContextInterface) {
            if (!$this->getContext()->isAllowed()) {
                self::getHook()->fire(hook::EVENT_APP_RUN_FORBIDDEN);
                // TODO: redirect?
                return false;
            }
            return true;
        } elseif (!$this->getContext()->isAllowed() && !self::getConfig()->get('context>' . static::getRequest()->getData('context') . '>view>' . static::getRequest()->getData('view') . '>public')) {
            self::getHook()->fire(hook::EVENT_APP_RUN_FORBIDDEN);
            static::getResponse()->setRedirect(static::getApp(), 'login');
            static::getResponse()->doRedirect();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns an instance of the context that is in the request container
     * @return context
     * @throws ReflectionException
     * @throws exception
     */
    protected function getContext(): context
    {
        $context = self::getRequest()->getData('context');

        if (count($errors = self::getValidator('text_methodname')->validate($context)) > 0) {
            throw new exception(self::EXCEPTION_GETCONTEXT_CONTEXTNAMEISINVALID, exception::$ERRORLEVEL_FATAL, $errors);
        }

        $simplename = self::getApp() . "_$context";

        if (!array_key_exists($simplename, $_REQUEST['instances'])) {
            $filename = self::getHomedir() . "backend/class/context/$context.php";

            if (!self::getFilesystem()->fileAvailable($filename)) {
                //
                // Check for existence in core (inherited) instead of CURRENT app
                // Overriding the default behavior
                //
                $baseFilename = dirname(__DIR__, 2) . "/backend/class/context/$context.php";
                if (self::getFilesystem()->fileAvailable($baseFilename)) {
                    // TODO: check if this can be non-hardcoded!
                    $classname = "\\codename\\core\\context\\$context";
                    $_REQUEST['instances'][$simplename] = new $classname();
                    return $_REQUEST['instances'][$simplename];
                } else {
                    throw new exception(self::EXCEPTION_GETCONTEXT_REQUESTEDCLASSFILENOTFOUND, exception::$ERRORLEVEL_FATAL, $filename);
                }
            }
            $classname = (static::getNamespace() ?? ("\\" . self::getVendor() . "\\" . self::getApp())) . "\\context\\$context";
            $_REQUEST['instances'][$simplename] = new $classname();
        }
        return $_REQUEST['instances'][$simplename];
    }

    /**
     * Returns a filesystem client. Uses the client identified by 'default' when $identifier is not passed in
     * @param string $identifier
     * @return filesystem
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getFilesystem(string $identifier = 'local'): filesystem
    {
        $object = self::getClient('filesystem', $identifier);
        if ($object instanceof filesystem) {
            return $object;
        }
        throw new exception('EXCEPTION_GETFILESYSTEM_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * the main run / main lifecycle (context, action, view, show - output)
     * @throws ReflectionException
     * @throws exception
     */
    protected function mainRun(): void
    {
        if ($this->getContext() instanceof customContextInterface || $this->getContext() instanceof taskschedulerInterface) {
            $this->doContextRun();
        } else {
            $this->doAction()->doView();
        }
        $this->doShow()->doOutput();
    }

    /**
     * [doContextRun description]
     * @return app
     * @throws ReflectionException
     * @throws exception
     */
    protected function doContextRun(): app
    {
        $context = $this->getContext();
        if ($context instanceof customContextInterface || $context instanceof taskschedulerInterface) {
            $context->run();
        }
        return $this;
    }

    /**
     * Tries to call the function that belongs to the view
     * @return app
     * @throws ReflectionException
     * @throws exception
     */
    protected function doView(): app
    {
        $view = static::getRequest()->getData('view');

        if (count($errors = static::getValidator('text_methodname')->reset()->validate($view)) > 0) {
            throw new exception(self::EXCEPTION_DOVIEW_VIEWNAMEISINVALID, exception::$ERRORLEVEL_FATAL, $errors);
        }

        $viewMethod = "view_$view";

        if (!method_exists($this->getContext(), $viewMethod)) {
            throw new exception(self::EXCEPTION_DOVIEW_VIEWFUNCTIONNOTFOUNDINCONTEXT, exception::$ERRORLEVEL_ERROR, $viewMethod);
        }

        if (app::getConfig()->exists('context>' . static::getRequest()->getData('context') . '>view>' . $view . '>_security>group')) {
            if (!app::getAuth()->memberOf(app::getConfig()->get('context>' . static::getRequest()->getData('context') . '>view>' . $view . '>_security>group'))) {
                throw new exception(self::EXCEPTION_DOVIEW_VIEWDISALLOWED, exception::$ERRORLEVEL_ERROR, ['context' => static::getRequest()->getData('context'), 'view' => $view]);
            }
        }

        $this->getContext()->$viewMethod();
        static::getHook()->fire(hook::EVENT_APP_DOVIEW_FINISH);
        return $this;
    }

    /**
     * Returns the auth instance that is configured as $identifier
     * @param string $identifier
     * @return auth
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getAuth(string $identifier = 'default'): auth
    {
        $object = self::getClient('auth', $identifier);
        if ($object instanceof auth) {
            return $object;
        }
        throw new exception('EXCEPTION_GETAUTH_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Tries to perform the action if it was set
     * @return app
     * @throws ReflectionException
     * @throws exception
     */
    protected function doAction(): app
    {
        $action = static::getRequest()->getData('action');
        if (is_null($action)) {
            return $this;
        }

        if (count($errors = static::getValidator('text_methodname')->reset()->validate($action)) > 0) {
            throw new exception(self::EXCEPTION_DOACTION_ACTIONNAMEISINVALID, exception::$ERRORLEVEL_FATAL, $errors);
        }

        if (!$this->actionExists(new contextname(static::getRequest()->getData('context')), new actionname($action))) {
            throw new exception(self::EXCEPTION_DOACTION_ACTIONNOTFOUNDINCONTEXT, exception::$ERRORLEVEL_NORMAL, $action);
        }

        $action = "action_$action";

        if (!method_exists($this->getContext(), $action)) {
            throw new exception(self::EXCEPTION_DOACTION_REQUESTEDACTIONFUNCTIONNOTFOUND, exception::$ERRORLEVEL_ERROR, $action);
        }

        $this->getContext()->$action();

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param contextname $context
     * @param actionname $action
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @todo clearly context related. move to the context
     * @see app_interface::actionExists, $action)
     */
    public function actionExists(contextname $context, actionname $action): bool
    {
        return self::getConfig()->exists("context>" . $context->get() . ">action>" . $action->get());
    }

    /**
     * Outputs the current request's template
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function doOutput(): void
    {
        if (!(static::getResponse() instanceof response\json)) {
            if (static::getResponse()->isDefined('templateengine')) {
                $templateengine = static::getResponse()->getData('templateengine');
            } else {
                $templateengine = app::getConfig()->get('defaulttemplateengine');
            }
            if ($templateengine == null) {
                $templateengine = 'default';
            }

            self::getResponse()->setOutput(app::getTemplateEngine($templateengine)->renderTemplate(static::getResponse()->getData('template'), static::getResponse()));
        }
        self::getResponse()->pushOutput();
    }

    /**
     * Returns the templateengine instance that is configured as $identifier
     * @param string $identifier
     * @return templateengine
     * @throws ReflectionException
     * @throws exception
     */
    final public static function getTemplateEngine(string $identifier = 'default'): templateengine
    {
        $object = self::getClient('templateengine', $identifier);
        if ($object instanceof templateengine) {
            return $object;
        }
        throw new exception('EXCEPTION_GETTEMPLATEENGINE_WRONG_OBJECT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * Loads the view's output file
     * @return app
     * @throws ReflectionException
     * @throws exception
     */
    protected function doShow(): app
    {
        if (static::getResponse()->isDefined('templateengine')) {
            $templateengine = static::getResponse()->getData('templateengine');
        } else {
            // look in view
            $templateengine = app::getConfig()->get('context>' . static::getResponse()->getData('context') . '>view>' . static::getResponse()->getData('view') . '>templateengine');
            // look in context
            if ($templateengine == null) {
                $templateengine = app::getConfig()->get('context>' . static::getResponse()->getData('context') . '>templateengine');
            }
            // fallback
            if ($templateengine == null) {
                $templateengine = 'default';
            }
        }

        static::getResponse()->setData('content', app::getTemplateEngine($templateengine)->renderView(static::getResponse()->getData('context') . '/' . static::getResponse()->getData('view')));
        return $this;
    }

    /**
     * Executes the given $context->$view
     * @param string $context
     * @param string $view
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function execute(string $context, string $view): void
    {
        static::getRequest()->setData('context', $context);
        static::getRequest()->setData('view', $view);
        $this->doView()->doShow();
    }

    /**
     * [initDebug description]
     * @return void [type] [description]
     */
    protected function initDebug(): void
    {
        if (self::getEnv() == 'dev' && (self::getRequest()->getData('template') !== 'json' && self::getRequest()->getData('template') !== 'blank')) {
            static::getHook()->add(hook::EVENT_APP_RUN_START, function () {
                $_REQUEST['start'] = microtime(true);
            })->add(hook::EVENT_APP_RUN_END, function () {
                if (static::getRequest() instanceof cli) {
                    echo 'Generated in ' . round(abs(($_REQUEST['start'] - microtime(true)) * 1000), 2) . 'ms' . chr(10)
                      . '  ' . \codename\core\observer\database::$query_count . ' Queries' . chr(10)
                      . '  ' . \codename\core\observer\cache::$set . ' Cache SETs' . chr(10)
                      . '  ' . \codename\core\observer\cache::$get . ' Cache GETs' . chr(10)
                      . '  ' . \codename\core\observer\cache::$hit . ' Cache HITs' . chr(10)
                      . '  ' . \codename\core\observer\cache::$miss . ' Cache MISSes' . chr(10);
                } elseif (static::getRequest() instanceof request\json || static::getRequest() instanceof \codename\rest\request\json) {
                    //
                    // NO DEBUG APPEND for this type of request/response
                    //
                } elseif (self::getRequest()->getData('template') !== 'json' && self::getRequest()->getData('template') !== 'blank') {
                    echo '<pre style="position: fixed; bottom: 0; right: 0; opacity:0.5;">Generated in ' . round(abs(($_REQUEST['start'] - microtime(true)) * 1000), 2) . 'ms
              ' . \codename\core\observer\database::$query_count . ' Queries
              ' . \codename\core\observer\cache::$set . ' Cache SETs
              ' . \codename\core\observer\cache::$get . ' Cache GETs
              ' . \codename\core\observer\cache::$hit . ' Cache HITs
              ' . \codename\core\observer\cache::$miss . ' Cache MISSes
              </pre>';
                }
            });
        }
    }
}
