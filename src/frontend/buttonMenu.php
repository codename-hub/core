<?php
namespace codename\core\frontend;

/**
 * Menu Button Link creation/link class
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-05
 */
class buttonMenu extends element {

  /**
   * array of menu elements
   * @var element[]
   */
  public $items = array();

  /**
   * @inheritDoc
   */
  public function __construct(array $items, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array())
  {
    $attributes['title'] = $title;
    $this->items = $items;
    $configArray = array(
      'title' => $title,
      'css' => $cssClasses,
      'attributes' => $attributes,
    );
    $value = parent::__construct($configArray);
    return $value;
  }

  /**
   * @inheritDoc
   */
  public function output(): string
  {
    $title = $this->config->get('title');
    $html = "<button type=\"button\" class=\"btn btn-xs btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
  	 <i class=\"icon icon-cogs\"></i> {$title} <span class=\"caret\"></span>
    </button>
    <ul class=\"dropdown-menu\">";
    foreach($this->items as $item) {
      $html .= "<li>" . $item->output() . "</li>";
    }
    $html .= "</ul>";
    return $html;
  }

  public function addItem(element $ele) {
    $this->items[] = $ele;
  }

  public static function getHtml(array $items, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array()) : string {
    $link = new self($items,$iconCss,$title,$cssClasses,$attributes);
    return $link->output();
  }

  public static function create(array $items, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array()) : element {
    $element = new self($items,$iconCss,$title,$cssClasses,$attributes);
    return $element;
  }

}
