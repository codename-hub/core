<?php
namespace codename\core\model\plugin;

use codename\core\exception;

/**
 * Apply data filters by fields on the results
 * @package core
 * @since 2018-02-14
 */
class fieldfilter extends \codename\core\model\plugin {

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
     * [allowedOperators description]
     * @var array
     */
    const allowedOperators = [
      '=',
      '!=',
      '>',
      '>=',
      '<',
      '<=',
    ];

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
        if(!\in_array($this->operator, static::allowedOperators)) {
          throw new exception('EXCEPTION_INVALID_OPERATOR', exception::$ERRORLEVEL_ERROR, $this->operator);
        }
        $this->conjunction = $conjunction;
        return $this;
    }

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
      return $tableAlias ? ($tableAlias . '.' . $this->field->get()) : $this->field->getValue();
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
      return $tableAlias ? ($tableAlias . '.' . $this->value->get()) : $this->value->getValue();
    }

}
