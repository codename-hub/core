<?php

namespace codename\core;

/**
 * This is the main API class. It will be used for the integration of foreign APIs, so here we will develop the clients
 * @package core
 * @since 2016-04-05
 */
class api
{
    /**
     * Instance of the well-known errorstack class
     * @var errorstack
     */
    protected $errorstack;

    /**
     * Holds application data
     * @var datacontainer
     */
    protected datacontainer $application;

    /**
     * @-param string $header_app
     * @-param string $header_auth
     * @-param string $header_token
     * @return api
     */
    public function __construct()
    {
        $this->errorstack = new errorstack($this->type);
        return $this;
    }

    /**
     * Return the version string
     * NOTE: this relies on URI-based API-Versioning!
     *
     * @return string
     * @example "v1"
     */
    public static function getVersion(): string
    {
        return explode('/', $_SERVER['REQUEST_URI'])[1];
    }

    /**
     * Return the endpoint of the request
     * @return string
     * @example $host/v1/user/disable?...  will return the endpoint "UserDisable"
     */
    public static function getEndpoint(): string
    {
        $endpoints = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
        unset($endpoints[1]);

        $ret = '';
        foreach ($endpoints as $endpoint) {
            $ret .= ucfirst($endpoint);
        }
        return $ret;
    }

    /**
     * Is a helper for the printAnswer function that fills the data
     * @param mixed $data
     * @return void
     * @throws exception
     */
    protected function printSuccess(mixed $data): void
    {
        $this->printAnswer(
            [
              'success' => 1,
              'data' => $data,
            ]
        );
    }

    /**
     * Outputs the JSON answer and ends the execution
     * @param array $data
     * @return void
     * @throws exception
     */
    protected function printAnswer(array $data): void
    {
        app::getResponse()->setHeader('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Is a helper for the printAnswer function that adds the 'error' key and fills it with the errorstack content
     * @param array $data
     * @return void
     * @throws exception
     */
    protected function printError(array $data = []): void
    {
        $this->printAnswer(
            [
              'success' => 0,
              'data' => $data,
              'errors' => $this->errorstack->getErrors(),
            ]
        );
    }
}
