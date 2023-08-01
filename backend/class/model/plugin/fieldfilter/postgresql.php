<?php

namespace codename\core\model\plugin\fieldfilter;

use codename\core\model\plugin\fieldfilter;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2023-05-25
 */
class postgresql extends fieldfilter
{

    /**
     * returns the left field value/name
     * @param string|null $tableAlias [the current table alias, if any]
     * @return string
     */
    public function getLeftFieldValue(string $tableAlias = null): string
    {
        // if tableAlias is set, return the field name prefixed with the alias
        // otherwise, just return the full modelfield value
        // TODO: check for cross-model filters...
        return $tableAlias ? ($tableAlias . '.' . '"' . $this->field->get() . '"') : '"' . str_replace('.', '"."', $this->field->getValue()) . '"';
    }

    /**
     * returns the right field value/name
     * @param string|null $tableAlias [the current table alias, if any]
     * @return string
     */
    public function getRightFieldValue(string $tableAlias = null): string
    {
        // if tableAlias is set, return the field name prefixed with the alias
        // otherwise, just return the full modelfield value
        // TODO: check for cross-model filters...
        return $tableAlias ? ($tableAlias . '.' . '"' . $this->field->get() . '"') : '"' . str_replace('.', '"."', $this->field->getValue()) . '"';
    }

}
