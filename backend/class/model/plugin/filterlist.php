<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\filterlist\filterlistInterface;
use codename\core\value\text\modelfield;

/**
 * Apply data filters on the results
 * @package core
 * @since 2016-02-04
 */
class filterlist extends plugin implements filterlistInterface
{
    /**
     * $field that is used to filterlist data from the model
     * @var null|modelfield $field
     */
    public ?modelfield $field = null;

    /**
     * Contains the value to searched in the $field
     * @var mixed
     */
    public mixed $value = null;

    /**
     * Contains the $operator for the $field
     * @var string
     */
    public string $operator = "=";

    /**
     * the conjunction to be used (AND, OR, XOR, ...)
     * may be null
     * @var null|string
     */
    public ?string $conjunction = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_filterlist::__construct(string $field, string $value, string $operator)
     */
    public function __construct(modelfield $field, mixed $value, string $operator, string $conjunction = null)
    {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
        $this->conjunction = $conjunction;
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
