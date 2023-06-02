<?php

namespace codename\core\log;

use codename\core\log;

/**
 * Logging client for static log files
 * @package core
 * @since 2016-01-15
 */
class file extends log implements logInterface
{
    /**
     * Contains all log instances by their file name
     * @var array of \codename\core\log
     */
    protected static array $instances = [];
    /**
     * Contains the logfile's path
     * @var null|string
     */
    protected ?string $file = null;

    /**
     * Constructor for the log class
     * @param array $config
     * @return log
     * @todo Use global var for the log path
     */
    protected function __construct(array $config)
    {
        parent::__construct($config);
        if (array_key_exists('minlevel', $config['data'])) {
            $this->minlevel = $config['data']['minlevel'];
        }
        $this->file = "/var/log/honeycomb/" . $config['data']['name'] . ".log";
        return $this;
    }

    /**
     * Returns the current instance by its name
     * @param array $config
     * @return log
     */
    public static function getInstance(array $config): log
    {
        if (!array_key_exists($config['data']['name'], self::$instances)) {
            self::$instances[$config['data']['name']] = new file($config);
        }
        return self::$instances[$config['data']['name']];
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::write($text, $level)
     */
    public function write(string $text, int $level): void
    {
        $ip = (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null);
        if (is_null($ip)) {
            $ip = (array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1");
        }
        $text = date("Y-m-d H:i:s") . ' - ' . gethostname() . ' - ' . $level . ' - ' . $ip . ' - ' . trim(str_replace(chr(13), '', str_replace(chr(10), '', $text))) . chr(10);
        @file_put_contents($this->file, $text, FILE_APPEND);
    }
}
