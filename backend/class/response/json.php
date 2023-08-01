<?php

namespace codename\core\response;

use codename\core\app;
use Exception;

/**
 * I handle all the data for a JSON response
 * @package core
 * @since 2016-05-31
 */
class json extends http
{
    /**
     * {@inheritDoc}
     * @param Exception $e
     * @throws \codename\core\exception
     */
    public function displayException(Exception $e): void
    {
        app::getResponse()->setStatuscode(500, "Internal Server Error");

        // log to stderr
        error_log(print_r($e, true));

        if (defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
            // TODO: optimize / check output?
            print_r(json_encode($e));
            die();
        } else {
            // TODO: show exception ?
        }


        $this->pushOutput();
    }

    /**
     * {@inheritDoc}
     */
    public function pushOutput(): void
    {
        http_response_code($this->translateStatusToHttpStatus());
        echo(json_encode($this->getData()));
    }
}
