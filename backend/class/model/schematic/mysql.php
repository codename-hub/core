<?php

namespace codename\core\model\schematic;

use codename\core\model\modelInterface;

/**
 * MySQL's specific SQL commands
 * @package core
 * @since 2017-03-01
 */
abstract class mysql extends sql implements modelInterface
{
    /**
     * @todo DOCUMENTATION
     */
    public const DB_TYPE = 'mysql';
}
