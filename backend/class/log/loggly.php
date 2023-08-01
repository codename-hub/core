<?php

namespace codename\core\log;

use codename\core\app;
use codename\core\exception;
use codename\core\log;
use ReflectionException;

/**
 * Logging client for loggly.com
 * @package core
 * @since 2016-04-05
 */
class loggly extends log implements logInterface
{
    /**
     * Contains all log instances by their file name
     * @var array of \codename\core\log
     */
    protected static array $instances = [];
    /**
     * Contains the token that is required for authentication
     * @var null|string
     */
    protected ?string $token = null;

    /**
     * Constructor for the log class
     * @param array $config
     * @return log
     */
    protected function __construct(array $config)
    {
        parent::__construct($config);
        $this->token = $config['data']['token'];
        if (array_key_exists('minlevel', $config['data'])) {
            $this->minlevel = $config['data']['minlevel'];
        }
        return $this;
    }

    /**
     * Returns the current instance by its name
     * @param array $config
     * @return log
     */
    public static function getInstance(array $config): log
    {
        if (!array_key_exists($config['data']['token'], self::$instances)) {
            self::$instances[$config['data']['token']] = new loggly($config);
        }
        return self::$instances[$config['data']['token']];
    }

    /**
     *
     * {@inheritDoc}
     * @param string $text
     * @param int $level
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\log_interface::write($text, $level)
     */
    public function write(string $text, int $level): void
    {
        $url = 'https://logs-01.loggly.com/inputs/' . $this->token . '/tag/http/';

        $data = [
          'app' => app::getApp(),
          'server' => gethostname(),
          'client' => $_SERVER['REMOTE_ADDR'],
          'level' => $level,
          'text' => $text,
        ];

        $data_string = json_encode($data);

        // create CURL instance
        $ch = curl_init();

        // Configure CURL instance
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'content-type:application/x-www-form-urlencoded',
          'Content-Length: ' . strlen($data_string),
        ]);
        curl_exec($ch);
        print_r(curl_error($ch));
        curl_close($ch);
    }
}
