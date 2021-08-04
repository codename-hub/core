<?php
namespace codename\core\model\plugin\aggregatefilter;

/**
 * Tell a model to filter aggregate field values
 * @package core
 * @author Kevin Dargel
 * @since 2021-03-23
 */
class sqlite extends \codename\core\model\plugin\aggregatefilter implements \codename\core\model\plugin\filter\filterInterface {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value = null, string $operator) {
    parent::__CONSTRUCT($field, $value, $operator);
    if($this->operator == 'ILIKE') {
      $this->operator = 'LIKE';
    }
    return $this;
  }
}
