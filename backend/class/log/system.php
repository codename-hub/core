<?php

namespace codename\core\log;

use codename\core\log;

class system extends log implements logInterface
{
    /**
     * Contains all log instances by their file name
     * @var log[]
     */
    protected static array $instances = [];

    /**
     * {@inheritDoc}
     */
    protected function __construct(array $config)
    {
        parent::__construct($config);
        if (array_key_exists('minlevel', $config['data'])) {
            $this->minlevel = $config['data']['minlevel'];
        }
    }

    /**
     * Returns the current instance by its name
     * @param array $config
     * @return log
     */
    public static function getInstance(array $config): log
    {
        if (!array_key_exists($config['data']['name'], self::$instances)) {
            self::$instances[$config['data']['name']] = new self($config);
        }
        return self::$instances[$config['data']['name']];
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $text, int $level): void
    {
        // only write, if ... you know.
        if ($level >= $this->minlevel) {
            error_log("[LOGDRIVER:SYSTEM] " . $text);
        }
    }
}
