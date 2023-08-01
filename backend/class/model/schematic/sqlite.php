<?php

namespace codename\core\model\schematic;

use codename\core\model\modelInterface;

/**
 * SQLite specific SQL commands
 * @package core
 * @since 2020-01-03
 */
abstract class sqlite extends sql implements modelInterface
{
    /**
     * @todo DOCUMENTATION
     */
    public const DB_TYPE = 'sqlite';
}
