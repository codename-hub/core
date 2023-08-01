<?php

namespace codename\core\frontend;

use codename\core\config;
use LogicException;

/**
 * (DOM) Element/Content Element base class
 * @package core
 * @since 2017-01-05
 */
class element
{
    /**
     * @var config
     */
    protected config $config;

    /**
     *
     */
    public function __construct(array $configArray = [])
    {
        $this->config = new config($configArray);
    }

    /**
     * @return string
     */
    public function output(): string
    {
        throw new LogicException("Method not implemented.");
    }
}
