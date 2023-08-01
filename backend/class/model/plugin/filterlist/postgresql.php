<?php

namespace codename\core\model\plugin\filterlist;

use codename\core\model\plugin\filterlist;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2023-05-25
 */
class postgresql extends filterlist implements filterlistInterface
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
