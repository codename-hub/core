<?php

namespace codename\core\model\plugin\calculatedfield;

use codename\core\model\plugin\calculatedfield;

/**
 * Tell a postgreSQL model to add a calculated field to the select query
 * @package core
 * @since 2017-05-18
 */
class postgresql extends calculatedfield implements calculatedfieldInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(): string
    {
        return $this->calculation . ' AS "' . $this->field->get() . '"';
    }
}
