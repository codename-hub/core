<?php
namespace codename\core\model\plugin\fulltext\filter;

use codename\core\exception;

/**
 * [mysql description]
 */
class mysql implements \codename\core\model\plugin\managedFilterInterface
{
  /**
   * $fields that are used to filter data from the model
   * @var \codename\core\value\text\modelfield[]
   */
  public $fields = null;

  /**
   * Contains the value to searched in the $field
   * @var string
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
   * @inheritDoc
   */
  public function getFilterQueryParameters(): array
  {
    return [
      'match_against' => $this->value,
    ];
  }

  /**
   * [__construct description]
   * @param \codename\core\value\text\modelfield[]|string[]  $fields      [description]
   * @param [type] $value       [description]
   * @param string|null $conjunction [description]
   */
  public function __construct(array $fields, $value = null, string $conjunction = null) {
    foreach($fields as &$thisfield) {
      if (!$thisfield instanceof \codename\core\value\text\modelfield) {
        $thisfield = \codename\core\value\text\modelfield::getInstance($thisfield);
      }
    }
    $this->fields = $fields;
    $this->value = $value;
    $this->operator = '>'; // by default, this value, and nothing else.
    $this->conjunction = $conjunction;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFilterQuery(array $variableNameMap, $tableAlias = null): string
  {
    $tableAlias = $tableAlias ? $tableAlias.'.' : '';
    $fields = [];
    foreach($this->fields as $field) {
      $fields[] = $tableAlias.$field->get();
    }
    $sql = 'MATCH ('.implode(', ', $fields).') AGAINST (:'.$variableNameMap['match_against'].' IN BOOLEAN MODE) '. $this->operator.' 0';
    return $sql;
  }
}
