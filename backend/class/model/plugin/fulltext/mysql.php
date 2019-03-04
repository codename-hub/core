<?php
namespace codename\core\model\plugin\fulltext;

use codename\core\model;
use codename\core\exception;

/**
 * Tell a MySQL model to add a calculated field to the select query
 * @package core
 * @author Ralf Thieme
 * @since 2019-03-04
 */
class mysql extends \codename\core\model\plugin\fulltext implements \codename\core\model\plugin\fulltext\fulltextInterface {

  /**
   * @inheritDoc
   */
  public function get(string $variableName, string $tableAlias = null) : string {
    $tableAlias = $tableAlias ? $tableAlias.'.' : '';
    $fields = [];
    foreach($this->fields as $field) {
      $fields[] = $tableAlias.$field->get();
    }
    $sql = 'MATCH ('.implode(', ', $fields).') AGAINST (:'.$variableName.' IN BOOLEAN MODE)';
    $alias = $this->field->get();
    if ($alias ?? false) {
      $sql .= ' AS '.$alias;
    }
    return $sql;
  }

  /**
   * @inheritDoc
   */
  public function getValue(): string
  {
    return $this->value;
  }

  /**
   * @inheritDoc
   */
  public function getField(): string
  {
    return $this->field->get();
  }

}
