<?php

namespace codename\core\model\plugin\filter;

use codename\core\model\plugin\filter;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2016-02-04
 */
class postgresql extends filter implements filterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFieldValue(?string $tableAlias = null): string
    {
        // Case sensitivity wrappers for PGSQL
        return $tableAlias ? ($tableAlias . '.' . '"' . $this->field->get() . '"') : '"' . str_replace('.', '"."', $this->field->getValue()) . '"';
    }
}
