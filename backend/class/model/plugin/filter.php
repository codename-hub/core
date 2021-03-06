<?php
namespace codename\core\model\plugin;

/**
 * Apply data filters on the results
 * @package core
 * @since 2016-02-04
 */
class filter extends \codename\core\model\plugin implements \codename\core\model\plugin\filter\filterInterface {

    /**
     * $field that is used to filter data from the model
     * @var \codename\core\value\text\modelfield
     */
    public $field = null;

    /**
     * Contains the value to searched in the $field
     * @var string
     */
    public $value = null;

    /**
     * Contains the $operator for the $field
     * @var string
     */
    public $operator = "=";

    /**
     * the conjunction to be used (AND, OR, XOR, ...)
     * may be null
     * @var string
     */
    public $conjunction = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_filter::__CONSTRUCT(string $field, string $value, string $operator)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value = null, string $operator, string $conjunction = null) {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
        $this->conjunction = $conjunction;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFieldValue(string $tableAlias = null): string
    {
      // if tableAlias is set, return the field name prefixed with the alias
      // otherwise, just return the full modelfield value
      // TODO: check for cross-model filters...
      return $tableAlias ? ($tableAlias . '.' . $this->field->get()) : $this->field->getValue();
    }

}
