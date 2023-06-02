<?php

namespace codename\core\request;

use codename\core\request;

/**
 * I handle all the data for an HTTP request
 * @package core
 * @since 2016-05-31
 */
class http extends request
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        //
        // HTTPS over external Loadbalancer Fix
        //
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        parent::__construct();
        $this->addData($_GET);
        $this->addData($_POST);
        $this->setData('lang', "de_DE");
    }
}
