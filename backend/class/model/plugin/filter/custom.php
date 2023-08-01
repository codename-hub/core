<?php

namespace codename\core\model\plugin\filter;

use codename\core\model\plugin\filter;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2017-03-01
 */
class custom extends filter implements filterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFieldValue(?string $tableAlias = null): string
    {
        // if tableAlias is set, return the field name prefixed with the alias
        // otherwise, just return the full modelfield value
        // TODO: check for cross-model filters...
        return $this->field->getValue();
    }
}
