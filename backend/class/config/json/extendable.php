<?php

namespace codename\core\config\json;

use codename\core\app;
use codename\core\config\json;
use codename\core\exception;
use ReflectionException;

/**
 * JSON Configuration that supports extending (via "extends" key)
 */
class extendable extends json
{
    /**
     * {@inheritDoc}
     *
     * @param string $file [description]
     * @param bool $appstack [description]
     * @param bool $inherit [description]
     * @param array|null $useAppstack [description]
     * @return extendable
     */
    public function __construct(string $file, bool $appstack = false, bool $inherit = false, ?array $useAppstack = null)
    {
        // do NOT start with an empty array
        // $config = [];
        $config = null;

        if (!$inherit && !$appstack) {
            $config = $this->decodeFile($this->getFullpath($file, $appstack));
            $config = $this->provideExtends($config, $appstack, $inherit, $useAppstack);
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
            if (realpath($file) !== false) {
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
            $thisConf = $this->provideExtends($thisConf, $appstack, $inherit, $useAppstack);
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

        $this->data = $config;
        return $this;
    }


    /**
     * [provideExtends description]
     * @param array|null $config [description]
     * @param bool $appstack [description]
     * @param bool $inherit [description]
     * @param array|null $useAppstack [description]
     * @return array|null               [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function provideExtends(?array $config, bool $appstack = false, bool $inherit = false, ?array $useAppstack = null): ?array
    {
        if ($config !== null && ($config['extends'] ?? false)) {
            $extends = is_array($config['extends']) ? $config['extends'] : [$config['extends']];
            foreach ($extends as $extend) {
                $extendableJsonConfig = new extendable($extend, $appstack, $inherit, $useAppstack);
                //
                // NOTE: this is a recursive replacement in an inheritance-like "extends"-manner
                // this means:
                // we inherit ('extend') another config and replace/add/extend keys with those from our current config
                // base config: some $extendableJsonConfig (we loop through the "extends" array)
                // config that replaces/adds keys: this one ($config)
                //
                $config = array_replace_recursive($extendableJsonConfig->get(), $config);
            }
        }
        if ($config !== null && ($config['mixins'] ?? false)) {
            $mixins = is_array($config['mixins']) ? $config['mixins'] : [$config['mixins']];
            foreach ($mixins as $mixin) {
                $extendableJsonConfig = new extendable($mixin, $appstack, $inherit, $useAppstack);
                $config = array_merge_recursive($config, $extendableJsonConfig->get());
            }
        }
        return $config;
    }
}
