<?php

namespace codename\core\model\plugin\filter;

use codename\core\exception;
use codename\core\model\plugin\filter;
use codename\core\value\text\modelfield;

use function in_array;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2017-03-01
 */
class mysql extends filter implements filterInterface
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
      'LIKE',
      'NOT LIKE',
    ];

    /**
     * {@inheritDoc}
     * @param modelfield $field
     * @param mixed $value
     * @param string $operator
     * @param string|null $conjunction
     * @throws exception
     */
    public function __construct(modelfield $field, mixed $value, string $operator, string $conjunction = null)
    {
        parent::__construct($field, $value, $operator, $conjunction);
        if (!in_array($this->operator, self::allowedOperators)) {
            throw new exception('EXCEPTION_INVALID_OPERATOR', exception::$ERRORLEVEL_ERROR, $this->operator);
        }
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
