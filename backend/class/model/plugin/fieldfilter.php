<?php
namespace codename\core\model\plugin;

/**
 * Apply data filters by fields on the results
 * @package core
 * @since 2018-02-14
 */
class fieldfilter extends \codename\core\model\plugin implements \codename\core\model\plugin\filter\filterInterface {

    /**
     * $field that is used to filter data from the model
     * @var \codename\core\value\text\modelfield
     */
    public $field = null;

    /**
     * Contains the field to compare to
     * @var \codename\core\value\text\modelfield
     */
    public $value = null;

    /**
     * Contains the $operator for the $field
     * @var string $operator
     */
    public $operator = "=";

    /**
     * the conjunction to be used (AND, OR, XOR, ...)
     * may be null
     * @var string $conjunction
     */
    public $conjunction = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_filter::__CONSTRUCT(string $field, string $value, string $operator)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value, string $operator, string $conjunction = null) {
        $this->field = $field;
        // TODO: Check for type of value ! must be \codename\core\value\text\modelfield
        $this->value = $value;
        $this->operator = $operator;
        $this->conjunction = $conjunction;
        return $this;
    }

}
