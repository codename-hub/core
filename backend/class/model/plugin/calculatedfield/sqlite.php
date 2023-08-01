<?php

namespace codename\core\model\plugin\calculatedfield;

use codename\core\model\plugin\calculatedfield;

/**
 * Tell a SQLite model to add a calculated field to the select query
 * @package core
 * @since 2021-03-19
 */
class sqlite extends calculatedfield implements calculatedfieldInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(): string
    {
        return $this->calculation . ' AS ' . $this->field->get();
    }
}
