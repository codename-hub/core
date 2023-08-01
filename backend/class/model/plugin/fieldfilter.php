<?php

namespace codename\core\model\plugin;

use codename\core\exception;
use codename\core\model\plugin;
use codename\core\value\text\modelfield;

use function in_array;

/**
 * Apply data filters by fields on the results
 * @package core
 * @since 2018-02-14
 */
class fieldfilter extends plugin
{
    /**
     * [allowedOperators description]
     * @var array
     */
    public const allowedOperators = [
      '=',
      '!=',
      '>',
      '>=',
      '<',
      '<=',
    ];
    /**
     * $field that is used to filter data from the model
     * @var null|modelfield
     */
    public ?modelfield $field = null;
    /**
     * Contains the field to compare to
     * @var null|modelfield
     */
    public ?modelfield $value = null;
    /**
     * Contains the $operator for the $field
     * @var string $operator
     */
    public string $operator = "=";
    /**
     * the conjunction to be used (AND, OR, XOR, ...)
     * may be null
     * @var null|string $conjunction
     */
    public ?string $conjunction = null;

    /**
     * @param modelfield $field
     * @param $value
     * @param string $operator
     * @param string|null $conjunction
     * @throws exception
     * @see \codename\core\model_plugin_filter::__construct(string $field, string $value, string $operator)
     */
    public function __construct(modelfield $field, $value, string $operator, string $conjunction = null)
    {
        $this->field = $field;
        // TODO: Check for type of value ! must be \codename\core\value\text\modelfield
        $this->value = $value;
        $this->operator = $operator;
        if (!in_array($this->operator, static::allowedOperators)) {
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
