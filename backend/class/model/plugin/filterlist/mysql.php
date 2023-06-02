<?php

namespace codename\core\model\plugin\filterlist;

use codename\core\model\plugin\filterlist;
use codename\core\value\text\modelfield;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2017-03-01
 */
class mysql extends filterlist implements filterlistInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(modelfield $field, mixed $value, string $operator, string $conjunction = null)
    {
        parent::__construct($field, $value, $operator, $conjunction);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldValue(?string $tableAlias = null): string
    {
        // if tableAlias is set, return the field name prefixed with the alias
        // otherwise, just return the full modelfield value
        // TODO: check for cross-model filters...
        return $tableAlias ? ($tableAlias . '.' . $this->field->get()) : $this->field->getValue();
    }
}
