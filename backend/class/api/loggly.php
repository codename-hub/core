<?php

namespace codename\core\api;

use codename\core\app;
use codename\core\exception;
use ReflectionException;

/**
 * @package core
 * @since 2016-04-05
 */
class loggly
{
    /**
     * Contains the API authentication token for Loggly
     * @var string
     * @todo make this available from Constructor!
     */
    protected string $token = '___ENTER_TOKEN_HERE___';

    /**
     * Sends data to Loggly.
     * @param array $data
     * @param int $level
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function send(array $data, int $level): void
    {
        $url = 'https://logs-01.loggly.com/inputs/' . $this->token . '/tag/http/';

        $data = [
          'app' => app::getApp(),
          'server' => gethostname(),
          'client' => $_SERVER['REMOTE_ADDR'],
          'level' => $level,
          'data' => $data,
        ];

        // create CURL instance
        $ch = curl_init($url);

        // Configure CURL instance
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'content-type:application/x-www-form-urlencoded',
          'Content-Length: ' . strlen(json_encode($data)),
        ]);
        curl_exec($ch);
        print_r(curl_error($ch));
        curl_close($ch);
    }
}
