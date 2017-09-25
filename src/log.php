<?php
namespace codename\core;

/**
 * Logging is a very important core of a SaaS project.
 * @package core
 * @since 2016-01-15
 */
abstract class log implements \codename\core\log\logInterface {
    
    /**
     * Contains the emergency log level number
     * @var number
     */
    CONST EMERGENCY = 5;
    
    /**
     * Contains the alert log level number
     * @var number
     */
    CONST ALERT = 4;
    
    /**
     * Contains the critical log level number
     * @var number
     */
    CONST CRITICAL = 3;
    
    /**
     * Contains the error log level number
     * @var number
     */
    CONST ERROR = 2;
    
    /**
     * Contains the warning log level number
     * @var number
     */
    CONST WARNING = 1;
    
    /**
     * Contains the notice log level number
     * @var number
     */
    CONST NOTICE = 0;
    
    /**
     * Contains the info log level number
     * @var number
     */
    CONST INFO = -1;
    
    /**
     * Contains the debug log level number
     * @var number
     */
    CONST DEBUG = -2;
    
    /**
     * Contains the minimum level that is required make the log client write log entries to the storage
     * @var int
     */
    protected $minlevel = 0;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::emergency($text)
     */
    public function emergency(string $text) {
        return $this->maskwrite($text, static::EMERGENCY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::alert($text)
     */
    public function alert(string $text) {
        return $this->maskwrite($text, static::ALERT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::critical($text)
     */
    public function critical(string $text) {
        return $this->maskwrite($text, static::CRITICAL);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::error($text)
     */
    public function error(string $text) {
        return $this->maskwrite($text, static::ERROR);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::warning($text)
     */
    public function warning(string $text) {
        return $this->maskwrite($text, static::WARNING);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::notice($text)
     */
    public function notice(string $text) {
        return $this->maskwrite($text, static::NOTICE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::info($text)
     */
    public function info(string $text) {
        return $this->maskwrite($text, static::INFO);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::debug($text)
     */
    public function debug(string $text) {
        $text = round((microtime(true) - $_REQUEST['start']) * 1000, 4) . 'ms ' . $text;
        return $this->maskwrite($text, static::DEBUG);
    }
    
    /**
     * Decides whether to log or not to log this entry by checking it's level.
     * @param string $text
     * @param int $level
     */
    protected function maskwrite(string $text, int $level) {
        if($level < $this->minlevel) {
            return;
        }
        return $this->write($text, static::DEBUG);
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
