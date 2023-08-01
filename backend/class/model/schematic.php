<?php

namespace codename\core\model;

use codename\core\model;

/**
 * Handling schematic storages (SQL)
 * @package core
 * @since 2016-02-04
 */
abstract class schematic extends model
{
    /**
     * Contains the schema this model is based upon
     * @var null|string
     */
    public ?string $schema = null;

    /**
     * Contains the table this model is based upon
     * @var null|string
     */
    public ?string $table = null;
}
