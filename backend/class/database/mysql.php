<?php

namespace codename\core\database;

use codename\core\database;
use PDO;

/**
 * MySQL/MariaDB db driver
 * @package core
 * @since 2017-03-01
 */
class mysql extends database
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        $this->driver = 'mysql';
        parent::__construct($config);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultAttributes(): array
    {
        return [
            // CHANGED 2021-05-04: this fixes invalid rowcount
            // on UPDATE where nothing really changed
          PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ];
    }
}
