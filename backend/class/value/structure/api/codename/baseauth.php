<?php

namespace codename\core\value\structure\api\codename;

use codename\core\value\structure;

/**
 * Data Object for base authentication containing all the required info
 * @package core
 * @since 2016-11-08
 */
class baseauth extends structure
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'structure_api_codename_baseauth';

    /**
     * Returns the host name of the service provider
     * @return string
     */
    public function getApp(): string
    {
        return $this->value['app_name'];
    }

    /**
     * Returns the port number of the service provider
     * @return int
     */
    public function getSecret(): int
    {
        return $this->value['app_secret'];
    }
}
