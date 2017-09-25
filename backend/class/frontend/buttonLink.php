<?php
namespace codename\core\frontend;

/**
 * Button Link creation/link class, extended
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-05
 */
class buttonLink extends link {

  /**
   * @inheritDoc
   */
  public function __construct(array $urlParams, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array(), $content = '')
  {
    $content = "<span class=\"{$iconCss}\"></span>" . ($content != '' ? '&nbsp;' . $content : '');
    $value = parent::__construct($urlParams,$content,$title,$cssClasses,$attributes);
    return $value;
  }

  public static function getHtml(array $urlParams, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array(), $content = '') : string {
    $link = new self($urlParams,$iconCss,$title,$cssClasses,$attributes,$content);
    return $link->output();
  }

  public static function create(array $urlParams, $iconCss = '', $title = '', array $cssClasses = array(), array $attributes = array(), $content = '') : element {
    $link = new self($urlParams,$iconCss,$title,$cssClasses,$attributes,$content);
    return $link;
  }

}
