<?php

namespace codename\core\config;

use codename\core\app;
use codename\core\config;
use codename\core\exception;
use ReflectionException;

/**
 * Loading configuration from JSON files
 * @package core
 * @since 2016-05-02
 */
class json extends config
{
    /**
     * The requested file name cannot be found in the directories.
     * Consider using the appstack reverse search (pass $appstack = TRUE into the method)
     * @var string
     */
    public const EXCEPTION_GETFULLPATH_FILEMISSING = 'EXCEPTION_GETFULLPATH_FILEMISSING';

    /**
     * You succeeded finding a configuration file that is matching the desired file name.
     * Anyhow, the file that I managed to find is empty
     * @var string
     */
    public const EXCEPTION_DECODEFILE_FILEISEMPTY = 'EXCEPTION_DECODEFILE_FILEISEMPTY';

    /**
     * The file that I found is containing information.
     * Anyway, the given information cannot be resolved into a JSON object.
     * @var string
     */
    public const EXCEPTION_DECODEFILE_FILEISINVALID = 'EXCEPTION_DECODEFILE_FILEISINVALID';

    /**
     * You told the json class to inherit it's content by using the appstack
     * But you missed to allow the constructor to access the appstack.
     * @var string
     */
    public const EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR = 'EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR';

    /**
     * Exception thrown when no files could be found to construct a config
     * based on inheritance
     * @var string
     */
    public const EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND = 'EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND';
    /**
     * contains a list of elements (file paths) this config is composed of
     * (if inheritance was allowed during construction of this object)
     * @var string[]
     */
    protected $inheritance = [];

    /**
     * Creates a config instance and loads the given JSON configuration file as content
     * If $appstack is true, I will try loading the configuration from a parent app, if it does not exist in the current app
     * If $inherit is true, I will load all the configurations from parents, and the lower children will always overwrite the parents
     * @param string $file [relative path to file]
     * @param bool $appstack [traverse appstack, if needed]
     * @param bool $inherit [use inheritance]
     * @param array|null $useAppstack [optional: custom appstack]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(string $file, bool $appstack = false, bool $inherit = false, ?array $useAppstack = null)
    {
        // do NOT start with an empty array
        $config = null;

        if (!$inherit && !$appstack) {
            $config = $this->decodeFile($this->getFullpath($file, $appstack));
            $this->data = $config;
            return $this;
        }

        if ($inherit && !$appstack) {
            throw new exception(self::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR, exception::$ERRORLEVEL_FATAL, ['file' => $file, 'info' => 'Need Appstack to inherit config!']);
        }

        if ($useAppstack == null) {
            $useAppstack = app::getAppstack();
        }

        foreach (array_reverse($useAppstack) as $app) {
            // NOTE: this was originally thought for absolute path checking
            // we now really check for equality
            // to avoid relative-to-absolute path conversion
            // if the file exists in CWD!
            if ((($realpath = realpath($file)) !== false) && ($realpath == $file)) {
                $fullpath = $file;
            } else {
                $fullpath = app::getHomedir($app['vendor'], $app['app']) . $file;
            }

            if (!app::getInstance('filesystem_local')->fileAvailable($fullpath)) {
                continue;
            }

            // initialize config as empty array here
            // as this is the first found file in the hierarchy
            if ($config === null) {
                $config = [];
            }

            $thisConf = $this->decodeFile($fullpath);
            $this->inheritance[] = $fullpath;
            if ($inherit) {
                $config = array_replace_recursive($config, $thisConf);
            } else {
                $config = $thisConf;
                break;
            }
        }

        if ($config === null) {
            // config was not initialized during hierarchy traversal
            throw new exception(self::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND, exception::$ERRORLEVEL_FATAL, ['file' => $file, 'appstack' => $useAppstack]);
        }

        parent::__construct($config);
        return $this;
    }

    /**
     * I will decode the given file and return the array of configuration it holds.
     * @param string $fullpath
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function decodeFile(string $fullpath): array
    {
        $text = app::getInstance('filesystem_local')->fileRead($fullpath);

        if (strlen($text) == 0) {
            throw new exception(self::EXCEPTION_DECODEFILE_FILEISEMPTY, exception::$ERRORLEVEL_FATAL, $fullpath);
        }

        $json = json_decode($text, true);

        if (is_null($json)) {
            throw new exception(self::EXCEPTION_DECODEFILE_FILEISINVALID, exception::$ERRORLEVEL_FATAL, $fullpath);
        }

        return app::object2array($json);
    }

    /**
     * I will give you the lowest level full path that exists in the appstack.
     * If I don't use the appstack ($appstack = false), then I only search in the current app.
     * I will throw an exception, if neither in the app nor the appstack I can find the file
     * @param string $file
     * @param bool $appstack
     * @return string
     * @throws ReflectionException
     * @throws exception
     * @todo REFACTOR simplify
     */
    protected function getFullpath(string $file, bool $appstack): string
    {
        // direct absolute file path
        if (!$appstack && realpath($file) !== false) {
            return $file;
        }

        $fullpath = app::getHomedir() . $file;

        if (app::getInstance('filesystem_local')->fileAvailable($fullpath)) {
            return $fullpath;
        }

        if (!$appstack) {
            throw new exception(self::EXCEPTION_GETFULLPATH_FILEMISSING, exception::$ERRORLEVEL_FATAL, ['file' => $fullpath, 'info' => 'use appstack?']);
        }
        return app::getInheritedPath($file);
    }

    /**
     * returns an array of file paths this config is composed of
     * ordered from base to top level app
     * also contains the topmost app (e.g. the current app)
     * @return string[]
     */
    public function getInheritance(): array
    {
        return $this->inheritance;
    }
}
