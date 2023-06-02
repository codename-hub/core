<?php

namespace codename\core;

use codename\core\log\logInterface;

/**
 * Logging is a very important core of a SaaS project.
 * @package core
 * @since 2016-01-15
 */
abstract class log implements logInterface
{
    /**
     * Contains the emergency log level number
     * @var int
     */
    public const EMERGENCY = 5;

    /**
     * Contains the alert log level number
     * @var int
     */
    public const ALERT = 4;

    /**
     * Contains the critical log level number
     * @var int
     */
    public const CRITICAL = 3;

    /**
     * Contains the error log level number
     * @var int
     */
    public const ERROR = 2;

    /**
     * Contains the warning log level number
     * @var int
     */
    public const WARNING = 1;

    /**
     * Contains the notice log level number
     * @var int
     */
    public const NOTICE = 0;

    /**
     * Contains the info log level number
     * @var int
     */
    public const INFO = -1;

    /**
     * Contains the debug log level number
     * @var int
     */
    public const DEBUG = -2;

    /**
     * Contains the minimum level that is required make the log client write log entries to the storage
     * @var int
     */
    protected $minlevel = 0;

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __construct(array $config = [])
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::emergency
     */
    public function emergency(string $text)
    {
        return $this->maskwrite($text, static::EMERGENCY);
    }

    /**
     * Decides whether to log or not to log this entry by checking it's level.
     * @param string $text
     * @param int $level
     * @return void|null
     */
    protected function maskwrite(string $text, int $level)
    {
        if ($level < $this->minlevel) {
            return;
        }
        $this->write($text, $level);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::alert
     */
    public function alert(string $text)
    {
        return $this->maskwrite($text, static::ALERT);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::critical
     */
    public function critical(string $text)
    {
        return $this->maskwrite($text, static::CRITICAL);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::error
     */
    public function error(string $text)
    {
        return $this->maskwrite($text, static::ERROR);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::warning
     */
    public function warning(string $text)
    {
        return $this->maskwrite($text, static::WARNING);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::notice
     */
    public function notice(string $text)
    {
        return $this->maskwrite($text, static::NOTICE);
    }

    /**
     *
     * {@inheritDoc}
     * @see log_interface::info
     */
    public function info(string $text)
    {
        return $this->maskwrite($text, static::INFO);
    }

    /**
     * @see log_interface::debug
     */
    public function debug(string $text)
    {
        $text = round((microtime(true) - ($_REQUEST['start'] ?? $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 4) . 'ms ' . $text;
        return $this->maskwrite($text, static::DEBUG);
    }

    /**
     * Access denied from outside this class
     * @see https://en.wikipedia.org/wiki/Singleton_pattern
     */
    protected function __clone()
    {
    }
}
