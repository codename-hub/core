<?php

namespace codename\core\response;

use codename\core\app;
use codename\core\exception;
use codename\core\helper\clicolors;
use codename\core\response;

/**
 * I handle all the data for a CLI response
 * @package core
 * @since 2016-05-31
 */
class cli extends response
{
    /**
     * {@inheritDoc}
     * output to cli/console
     */
    public function pushOutput(): void
    {
        echo $this->getOutput();
        app::setExitCode($this->translateStatus());
    }

    /**
     * {@inheritDoc}
     */
    protected function translateStatus(): int
    {
        $translate = [
          self::STATUS_SUCCESS => 0,
          self::STATUS_INTERNAL_ERROR => 1,
          self::STATUS_NOTFOUND => 1, // ?
        ];
        return $translate[$this->status];
    }

    /**
     * {@inheritDoc}
     */
    public function setHeader(string $header)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function displayException(\Exception $e): void
    {
        $formatter = new clicolors();

        // log to stderr
        error_log(print_r($e, true));

        if (defined('CORE_ENVIRONMENT')
            // && CORE_ENVIRONMENT != 'production'
        ) {
            echo $formatter->getColoredString("Hicks", 'red') . chr(10);
            echo $formatter->getColoredString("{$e->getMessage()} (Code: {$e->getCode()})", 'yellow') . chr(10) . chr(10);

            if (app::getRequest()->getData('verbose')) {
                if ($e instanceof exception && !is_null($e->info)) {
                    echo $formatter->getColoredString("Information", 'cyan') . chr(10);
                    echo chr(10);
                    print_r($e->info);
                    echo chr(10);
                }

                echo $formatter->getColoredString("Stacktrace", 'cyan') . chr(10);
                echo chr(10);
                print_r($e->getTrace());
                echo chr(10);
            }
            die();
        }
    }
}
