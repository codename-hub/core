<?php

namespace codename\core;

/**
 * Here we can store callables that will be called when we fire the given name
 * Is based on the singleton design pattern.
 * @package core
 * @since 2016-04-11
 */
class hook
{
    /**
     * This event will be fired whenever a translation key cannot be resolved into translated text
     * @var string
     */
    public const EVENT_TRANSLATE_TRANSLATION_KEY_MISSING = 'EVENT_TRANSLATE_TRANSLATION_KEY_MISSING';

    /**
     * This event will be fired when the view method has completed and the wrapping method is about to finish
     * @var string
     */
    public const EVENT_APP_DOVIEW_FINISH = 'EVENT_APP_DOVIEW_FINISH';

    /**
     * This event will be fired whenever the method adds an application to the current appstack.
     * @var string
     */
    public const EVENT_APP_MAKEAPPSTACK_ADDED_APP = 'EVENT_APP_MAKEAPPSTACK_ADDED_APP';

    /**
     * This event is fired in the app class constructor.
     * Use it to prepend actions before any action of the framework
     * @var string
     */
    public const EVENT_APP_INITIALIZING = 'EVENT_APP_INITIALIZING';

    /**
     * This event is fired after the app class constructor finishes its actions.
     * @var string
     */
    public const EVENT_APP_INITIALIZED = 'EVENT_APP_INITIALIZED';

    /**
     * This event is fired when the app class's >run method is executed.
     * Use it to prepend actions before the page generation
     * @var string
     */
    public const EVENT_APP_RUN_START = 'EVENT_APP_RUN_START';

    /**
     * This event will be fired whenever a user tries to open a view/action/context
     * and the context's isAllowed method returns false.
     * You might use this to intervene for different redirection or storing of the request.
     * @var string
     */
    public const EVENT_APP_RUN_FORBIDDEN = 'EVENT_APP_RUN_FORBIDDEN';

    /**
     * fired when the app enters the main app routine
     */
    public const EVENT_APP_RUN_MAIN = 'EVENT_APP_RUN_MAIN';

    /**
     * This event is fired whenever the run method finishes all it's actions
     * @var string
     */
    public const EVENT_APP_RUN_END = 'EVENT_APP_RUN_END';

    /**
     * This event is fired, when you try loading an object from a model
     * but the model's primarykey is missing in the request container
     * @var string
     */
    public const EVENT_APP_GETMODELOBJET_ARGUMENT_NOT_FOUND = 'EVENT_APP_GETMODELOBJET_ARGUMENT_NOT_FOUND';

    /**
     * This event is fired, when you try loading an object from a model
     * but the primary key (contained in the request container) cannot be found in the model
     * @var string
     */
    public const EVENT_APP_GETMODELOBJET_ENTRY_NOT_FOUND = 'EVENT_APP_GETMODELOBJET_ENTRY_NOT_FOUND';

    /**
     * This event will be fired when a cache object is requested but not found.
     * @param string $key
     * @var string
     */
    public const EVENT_CACHE_MISS = 'EVENT_CACHE_MISS';

    /**
     * This event will be fired when the CRUD class creates a form in the credit method.
     * You can use this to alter the given form instance for asking more fields
     * @var string
     */
    public const EVENT_CRUD_CREATE_FORM_INIT = 'EVENT_CRUD_CREATE_FORM_INIT';

    /**
     * This event will be fired whenever the edit method of any CRUD instance generates a form instance.
     * Use this event to alter the current form of the CRUD instance (e.g. for asking for more fields)
     * @var string
     */
    public const EVENT_CRUD_EDIT_FORM_INIT = 'EVENT_CRUD_EDIT_FORM_INIT';

    /**
     * This event is fired whenever the CRUD generator wants to create an entry
     * to a model. It is given the $data and must return the $data.
     * @example Imagine cases where you don't want a user to input data, but you must
     * add it to the entry, because the missing fields would violate the model's
     * constraints. Here you can do anything you want with the entry array.
     * @var string
     */
    public const EVENT_CRUD_CREATE_BEFORE_VALIDATION = 'EVENT_CRUD_CREATE_BEFORE_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * @var string
     */
    public const EVENT_CRUD_CREATE_AFTER_VALIDATION = 'EVENT_CRUD_CREATE_AFTER_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * We might run additional validators here.
     * output must be either null, empty array or errors found in additional validators
     * @var string
     */
    public const EVENT_CRUD_CREATE_VALIDATION = 'EVENT_CRUD_CREATE_VALIDATION';

    /**
     * This event is fired whenever the CRUD generator wants to create an entry
     * to a model. It is given the $data and must return the $data.
     * @example Imagine you want to manipulate entries on a model when saving the entry
     * from the CRUD generator. This is version will happen after the validation.
     * @var string
     */
    public const EVENT_CRUD_CREATE_BEFORE_SAVE = 'EVENT_CRUD_CREATE_BEFORE_SAVE';

    /**
     * This event is fired whenever the CRUD generator successfully creates an entry
     * to a model. It is given the $data.
     * @example Think of Creating an email using the complete entry after saving.
     * @var string
     */
    public const EVENT_CRUD_CREATE_SUCCESS = 'EVENT_CRUD_CREATE_SUCCESS';

    /**
     * This event is fired whenever the CRUD generator wants to edit an entry
     * to a model. It is given the $data and must return the $data.
     * @example Imagine cases where you don't want a user to input data, but you must
     * add it to the entry, because the missing fields would violate the model's
     * constraints. Here you can do anything you want with the entry array.
     * @var string
     */
    public const EVENT_CRUD_EDIT_BEFORE_VALIDATION = 'EVENT_CRUD_EDIT_BEFORE_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * @var string
     */
    public const EVENT_CRUD_EDIT_AFTER_VALIDATION = 'EVENT_CRUD_EDIT_AFTER_VALIDATION';

    /**
     * This event is fired after validation has been successful.
     * We might run additional validators here.
     * output must be either null, empty array or errors found in additional validators
     * @var string
     */
    public const EVENT_CRUD_EDIT_VALIDATION = 'EVENT_CRUD_EDIT_VALIDATION';

    /**
     * This event is fired whenever the CRUD generator wants to edit/update an entry
     * in a model. It is given the $data and must return the $data.
     * @example Imagine you want to manipulate entries on a model when saving the entry
     * from the CRUD generator. This is happened after the validation.
     * @var string
     */
    public const EVENT_CRUD_EDIT_BEFORE_SAVE = 'EVENT_CRUD_EDIT_BEFORE_SAVE';

    /**
     * This event is fired whenever the CRUD generator successfully edited an entry
     * to a model. It is given the $data.
     * @example Think of Creating an email using the updated entry after saving.
     * @var string
     */
    public const EVENT_CRUD_EDIT_SUCCESS = 'EVENT_CRUD_EDIT_SUCCESS';

    /**
     * This event will be fired when the database controller tries executing a query.
     * It will be fired before the actual query is executed.
     * @var string
     */
    public const EVENT_DATABASE_QUERY_QUERY_BEFORE = 'EVENT_DATABASE_QUERY_QUERY_BEFORE';

    /**
     * This event will be fired when the database controller successfully executed a database query
     * It will be fired after the actual query is executed.
     * @var string
     */
    public const EVENT_DATABASE_QUERY_QUERY_AFTER = 'EVENT_DATABASE_QUERY_QUERY_AFTER';

    /**
     * This event is fired after the API Service provider validated that the
     * external application's authentication data was sent as headers.
     * Use it to modify the headers before validating the header match.
     * @var string
     */
    public const EVENT_API_AUTHENTICATE_HEADER_MODIFY = 'EVENT_API_AUTHENTICATE_HEADER_MODIFY';

    /**
     * This event is fired in the authenticate method of an API service provider.
     * Use it to modify the salt value during runtime.
     * It will be given the current request instance.
     * If it returns a string, this string will be used as the salt
     * @var string
     */
    public const EVENT_API_AUTHENTICATE_SALT_MODIFY = 'EVENT_API_AUTHENTICATE_SALT_MODIFY';
    /**
     * Contains the actual instance
     * @var null|hook
     */
    private static ?hook $instance = null;
    /**
     * Contains a list of hooks
     * @var array of callables
     */
    private array $hooks = [];

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __construct()
    {
    }

    /**
     * Returns the instance of the hook
     * @return hook
     */
    public static function getInstance(): hook
    {
        if (is_null(self::$instance)) {
            self::$instance = new hook();
        }
        return self::$instance;
    }

    /**
     * Adds the $callable function to the hook $name
     * @param string $name
     * @param callable $callback
     * @return hook
     */
    public function add(string $name, callable $callback): hook
    {
        $this->hooks[$name][] = $callback;
        return $this;
    }

    /**
     * Fires all the callbacks that are stored under the given $name
     * @param string $name
     * @param null $arguments
     * @return mixed
     */
    public function fire(string $name, $arguments = null): mixed
    {
        $ret = null;
        foreach ($this->get($name) as $callback) {
            if (!is_callable($callback)) {
                continue;
            }
            $ret = call_user_func($callback, $arguments);
        }
        return $ret;
    }

    /**
     * Returns all the callbacks that are stored under the given $name
     * @param string $name
     * @return array
     */
    public function get(string $name): array
    {
        return $this->hooks[$name] ?? [];
    }

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __clone()
    {
    }
}
