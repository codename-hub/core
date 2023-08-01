<?php

namespace codename\core;

use LogicException;
use ReflectionException;

/**
 * [templateengine description]
 */
abstract class templateengine
{
    /**
     * config
     * @var null|config
     */
    protected ?config $config = null;

    /**
     * name of config validator for this template engine
     * @var string|null
     */
    protected ?string $configValidator = null;

    /**
     * [__construct description]
     * @param array $config [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $config = [])
    {
        // validate config on need
        if ($this->configValidator != null) {
            $validator = app::getValidator($this->configValidator);
            if (count($validator->validate($config)) > 0) {
                throw new exception("CORE_TEMPLATEENGINE_CONFIG_VALIDATION_FAILED", exception::$ERRORLEVEL_FATAL, $config);
            }
        }

        $this->config = new config($config);
    }

    /**
     * Returns the path for storing (temporary) assets
     * for rendering or output
     * @return string [description]
     */
    public function getAssetsPath(): string
    {
        throw new LogicException('Not implemented');
    }

    /**
     * [getConfig description]
     * @return config [description]
     */
    public function getConfig(): config
    {
        return $this->config;
    }

    /**
     * [render description]
     * @param string $referencePath [path to view, without file extension]
     * @param array|datacontainer|null $data [data container / data context]
     * @return string                  [rendered view]
     */
    abstract public function render(string $referencePath, array|datacontainer|null $data = null): string;

    /**
     * [renderView description]
     * @param string $viewPath [path to view, without file extension]
     * @param array|datacontainer|null $data [data container / data context]
     * @return string                  [rendered view]
     */
    abstract public function renderView(string $viewPath, array|datacontainer|null $data = null): string;

    /**
     * [renderTemplate description]
     * @param string $templatePath [path to template, without file extension]
     * @param array|datacontainer|null $data [data container / data context]
     * @return string                      [rendered template]
     */
    abstract public function renderTemplate(string $templatePath, array|datacontainer|null $data = null): string;
}
