<?php

namespace codename\core\database;

use codename\core\database;

/**
 * PostgreSQL db driver
 * @package core
 * @since 2016-02-05
 */
class postgresql extends database
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        $this->driver = 'postgresql';
        parent::__construct($config);
        return $this;
    }
}
