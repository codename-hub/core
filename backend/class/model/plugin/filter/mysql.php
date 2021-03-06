<?php
namespace codename\core\model\plugin\filter;

use codename\core\exception;

/**
 * Tell a model to filter the results
 * @package core
 * @author Kevin Dargel
 * @since 2017-03-01
 */
class mysql extends \codename\core\model\plugin\filter implements \codename\core\model\plugin\filter\filterInterface {

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
    'LIKE',
    'NOT LIKE',
  ];

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value = null, string $operator, string $conjunction = null) {
    parent::__CONSTRUCT($field, $value, $operator, $conjunction);
    if($this->operator == 'ILIKE') {
      $this->operator = 'LIKE';
    }
    if(!\in_array($this->operator, self::allowedOperators)) {
      throw new exception('EXCEPTION_INVALID_OPERATOR', exception::$ERRORLEVEL_ERROR, $this->operator);
    }
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
