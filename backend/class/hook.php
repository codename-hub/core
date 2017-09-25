<?php
namespace codename\core;

/**
 * Here we can store callables that will be called when we fire the given name
 * <br />Is based on the singleton design pattern.
 * @package core
 * @since 2016-04-11
 */
class hook {

    /**
     * This event will be fired whenever a translation key cannot be resolved into translated text
     * @var string
     */
    const EVENT_TRANSLATE_TRANSLATION_KEY_MISSING = 'EVENT_TRANSLATE_TRANSLATION_KEY_MISSING';

    /**
     * This event will be fired when the view method has completed and the wrapping method is about to finish
     * @var string
     */
    const EVENT_APP_DOVIEW_FINISH = 'EVENT_APP_DOVIEW_FINISH';

    /**
     * This event will be fired whenever the method adds an application to the current appstack.
     * @var string
     */
    const EVENT_APP_MAKEAPPSTACK_ADDED_APP = 'EVENT_APP_MAKEAPPSTACK_ADDED_APP';

    /**
     * This event is fired in the app class constructor.
     * <br />Use it to prepend actions before any action of the framework
     * @var string
     */
    const EVENT_APP_INITIALIZING = 'EVENT_APP_INITIALIZING';

    /**
     * This event is fired after the app class constructor finishes it's actions.
     * @var string
     */
    const EVENT_APP_INITIALIZED = 'EVENT_APP_INITIALIZED';

    /**
     * This event is fired when the app class's >run method is executed.
     * <br />Use it to prepend actions before the page generation
     * @var string
     */
    const EVENT_APP_RUN_START = 'EVENT_APP_RUN_START';

    /**
     * This event will be fired whenever a user tries to open a view/action/context
     * <br />and the context's isAllowed method returns false.
     * <br />You might use this to intervene for different redirection or storing of the request.
     * @var string
     */
    const EVENT_APP_RUN_FORBIDDEN = 'EVENT_APP_RUN_FORBIDDEN';

    /**
     * fired when the app enters the main app routine
     */
    const EVENT_APP_RUN_MAIN = 'EVENT_APP_RUN_MAIN';

    /**
     * This event is fired whenever the run method finishes all it's actions
     * @var string
     */
    const EVENT_APP_RUN_END = 'EVENT_APP_RUN_END';

    /**
     * This event is fired, when you try loading an object from a model
     * <br />but the model's primarykey is missing in the request container
     * @var string
     */
    const EVENT_APP_GETMODELOBJET_ARGUMENT_NOT_FOUND = 'EVENT_APP_GETMODELOBJET_ARGUMENT_NOT_FOUND';

    /**
     * This event is fired, when you try loading an object from a model
     * <br />but the primary key (contained in the request container) cannot be found in the model
     * @var string
     */
    const EVENT_APP_GETMODELOBJET_ENTRY_NOT_FOUND = 'EVENT_APP_GETMODELOBJET_ENTRY_NOT_FOUND';

    /**
     * This event will be fired when a cache object is requested but not found.
     * @param string $key
     * @var string
     */
    const EVENT_CACHE_MISS = 'EVENT_CACHE_MISS';

    /**
     * This event will be fired when the CRUD class creates a form in the credit method.
     * <br />You can use this to alter the given form instance for asking more fields
     * @var string
     */
    const EVENT_CRUD_CREATE_FORM_INIT = 'EVENT_CRUD_CREATE_FORM_INIT';

    /**
     * This event will be fired whenever the edit method of any CRUD instance generates a form instance.
     * <br />Use this event to alter the current form of the CRUD instance (e.g. for asking for more fields)
     * @var string
     */
    const EVENT_CRUD_EDIT_FORM_INIT = 'EVENT_CRUD_EDIT_FORM_INIT';

    /**
     * This event is fired whenever the CRUD generator wants to create an entry
     * <br />to a model. It is given the $data and must return the $data.
     * @example Imagine cases where you don't want a user to input data but you must
     * <br />add it to the entry, because the missing fields would violate the model's
     * <br />constraints. Here you can do anything you want with the entry array.
     * @var string
     */
    const EVENT_CRUD_CREATE_BEFORE_VALIDATION = 'EVENT_CRUD_CREATE_BEFORE_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * @var string
     */
    const EVENT_CRUD_CREATE_AFTER_VALIDATION = 'EVENT_CRUD_CREATE_AFTER_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * We might run additional validators here.
     * output must be either null, empty array or errors found in additional validators
     * @var string
     */
    const EVENT_CRUD_CREATE_VALIDATION = 'EVENT_CRUD_CREATE_VALIDATION';

    /**
     * This event is fired whenever the CRUD generator wants to create an entry
     * <br />to a model. It is given the $data and must return the $data.
     * @example Imagine you want to manipulate entries on a model when saving the entry
     * <br />from the CRUD generator. This is version will happen after the validation.
     * @var string
     */
    const EVENT_CRUD_CREATE_BEFORE_SAVE = 'EVENT_CRUD_CREATE_BEFORE_SAVE';

    /**
     * This event is fired whenever the CRUD generator successfully creates an entry
     * <br />to a model. It is given the $data.
     * @example Think of Creating an email using the complete entry after saving.
     * @var string
     */
    const EVENT_CRUD_CREATE_SUCCESS = 'EVENT_CRUD_CREATE_SUCCESS';

    /**
     * This event is fired whenever the CRUD generator wants to edit an entry
     * <br />to a model. It is given the $data and must return the $data.
     * @example Imagine cases where you don't want a user to input data but you must
     * <br />add it to the entry, because the missing fields would violate the model's
     * <br />constraints. Here you can do anything you want with the entry array.
     * @var string
     */
    const EVENT_CRUD_EDIT_BEFORE_VALIDATION = 'EVENT_CRUD_EDIT_BEFORE_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * @var string
     */
    const EVENT_CRUD_EDIT_AFTER_VALIDATION = 'EVENT_CRUD_EDIT_AFTER_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * We might run additional validators here.
     * output must be either null, empty array or errors found in additional validators
     * @var string
     */
    const EVENT_CRUD_EDIT_VALIDATION = 'EVENT_CRUD_EDIT_VALIDATION';

    /**
     * This event is fired whenever the CRUD generator wants to edit/update an entry
     * <br />in a model. It is given the $data and must return the $data.
     * @example Imagine you want to manipulate entries on a model when saving the entry
     * <br />from the CRUD generator. This is will happen after the validation.
     * @var string
     */
    const EVENT_CRUD_EDIT_BEFORE_SAVE = 'EVENT_CRUD_EDIT_BEFORE_SAVE';

    /**
     * This event is fired whenever the CRUD generator successfully edited an entry
     * <br />to a model. It is given the $data.
     * @example Think of Creating an email using the updated entry after saving.
     * @var string
     */
    const EVENT_CRUD_EDIT_SUCCESS = 'EVENT_CRUD_EDIT_SUCCESS';

    /**
     * This event will be fired when the database controller tries executing a query.
     * <br />It will be fired before the actual query is executed.
     * @var string
     */
    const EVENT_DATABASE_QUERY_QUERY_BEFORE = 'EVENT_DATABASE_QUERY_QUERY_BEFORE';

    /**
     * This event will be fired when the database controller successfully executed a database query
     * <br />It will be fired after the actual query is executed.
     * @var string
     */
    const EVENT_DATABASE_QUERY_QUERY_AFTER = 'EVENT_DATABASE_QUERY_QUERY_AFTER';

    /**
     * This event is fired after the API Service provider validated that the
     * <br />external application's authentication data was sent as headers.
     * <br />Use it to modify the headers before validating the header match.
     * @var string
     */
    const EVENT_API_AUTHENTICATE_HEADER_MODIFY = 'EVENT_API_AUTHENTICATE_HEADER_MODIFY';

    /**
     * This event is fired in the authenticate method of an API service provider.
     * <br />Use it to modify the salt value during runtime.
     * <br />It will be given the current request instance.
     * <br />If it returns a string, this string will be used as the salt
     * @var string
     */
    const EVENT_API_AUTHENTICATE_SALT_MODIFY = 'EVENT_API_AUTHENTICATE_SALT_MODIFY';

    /**
     * Contains a list of hooks
     * @var array of callables
     */
    private $hooks = array();

    /**
     * Contains the actual instance
     * @var \codename\core\hook
     */
    private static $instance = null;

    /**
     * Adds the $callable function to the hook $name
     * @param string $name
     * @param callable $callback
     * @return \codename\core\hook
     */
    public function add(string $name, callable $callback) : \codename\core\hook {
        $this->hooks[$name][] = $callback;
        return $this;
    }

    /**
     * Returns all the callbacks that are stored under the given $name
     * @param string $name
     * @return array
     */
    public function get(string $name) : array {
        return isset($this->hooks[$name]) ? $this->hooks[$name] : array();
    }

    /**
     * Fires all the callbacks that are stored under the given $name
     * @param string $name
     * @return multitype
     */
    public function fire(string $name, $arguments = null) {
        $ret = null;
        foreach($this->get($name) as $callback) {
            if(!is_callable($callback)) {
                continue;
            }
            $ret = call_user_func($callback, $arguments);
        }
        return $ret;
    }

    /**
     * Returns the instance of the hook
     * @return \codename\core\hook
     */
    public static function getInstance() : \codename\core\hook {
        if(is_null(self::$instance)) {
            self::$instance = new \codename\core\hook();
        }
        return self::$instance;
    }

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __construct() {
        return;
    }

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __clone() {
        return;
    }

}
