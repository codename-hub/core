<?php
namespace codename\core\log;

/**
 * Definition for \codename\core\log
 * @package core
 * @since 2016-04-05
 */
interface logInterface {
    
    /**
     * Writes data to the emergency log
     * @param string $text content to write to the log file
     */
    public function emergency(string $text);

    /**
     * Writes data to the alert log
     * @param string $text content to write to the log file
     */
    public function alert(string $text);

    /**
     * Writes data to the critical log
     * @param string $text content to write to the log file
     */
    public function critical(string $text);

    /**
     * Writes data to the error log
     * @param string $text content to write to the log file
     */
    public function error(string $text);

    /**
     * Writes data to the warning log
     * @param string $text content to write to the log file
     */
    public function warning(string $text);

    /**
     * Writes data to the notice log
     * @param string $text content to write to the log file
     */
    public function notice(string $text);

    /**
     * Writes data to the info log
     * @param string $text content to write to the log file
     */
    public function info(string $text);
    
    /**
     * Writes the given text to the log file that was instanced before
     * @param string $text Text to be written to the index
     * @return void
     */
    public function write(string $text, int $level);
    
}
