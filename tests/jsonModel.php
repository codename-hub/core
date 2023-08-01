<?php

namespace codename\core\tests;

use codename\core\app;
use codename\core\config;
use codename\core\model\schemeless\json;

/**
 * JSON Base model
 * enables freely defining and loading model configs
 * for static json data
 */
class jsonModel extends json
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $file, string $prefix, string $name, array $config)
    {
        $this->useCache();
        $modeldata['appstack'] = app::getAppstack();
        $value = parent::__construct($modeldata);
        $this->config = new config($config);
        $this->setConfig($file, $prefix, $name);
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadConfig(): config
    {
        // has to be pre-set above
        return $this->config;
    }
}
