<?php

namespace codename\core\log;

use codename\core\log;

/**
 * Logging client for dummy/null output
 * @package core
 */
class dummy extends log implements logInterface
{
    /**
     * Contains all log instances by their file name
     * @var array of \codename\core\log
     */
    protected static array $instances = [];

    /**
     * Returns the current instance by its name
     * @param array $config
     * @return log
     */
    public static function getInstance(array $config): log
    {
        if (!array_key_exists($config['data']['name'], self::$instances)) {
            self::$instances[$config['data']['name']] = new dummy($config);
        }
        return self::$instances[$config['data']['name']];
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $text, int $level): void
    {
    }
}
