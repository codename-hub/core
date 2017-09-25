<?php
namespace codename\core\frontend;

/**
 * Button Group creation/link class
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-05
 */
class buttonGroup extends element {

  /**
   * array of menu elements
   * @var element[]
   */
  public $items = array();

  /**
   * @inheritDoc
   */
  public function __construct(array $items)
  {
    $this->items = $items;
    $configArray = array();
    $value = parent::__construct($configArray);
    return $value;
  }

  /**
   * @inheritDoc
   */
  public function output(): string
  {
    $html = "<div class=\"btn-group\">";
    foreach($this->items as $item) {
      $html .= $item->output();
    }
    $html .= "</div>";
    return $html;
  }

  public function addItem(element $ele) {
    $this->items[] = $ele;
  }

  public static function getHtml(array $items) : string {
    $element = new self($items);
    return $element->output();
  }

  public static function create(array $items) : element {
    $element = new self($items);
    return $element;
  }

}
