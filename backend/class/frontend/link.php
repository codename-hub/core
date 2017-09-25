<?php
namespace codename\core\frontend;

/**
 * Link creation/link class
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-05
 */
class link extends element {
  /**
   * @inheritDoc
   */
  public function output(): string
  {
    $href = http_build_query($this->config->get('params'));
    $css = implode(' ', $this->config->get('css'));
    $content = $this->config->get('content');
    $title = $this->config->get('title');

    $tempAttributes = array();
    foreach($this->config->get('attributes') as $attribute => $value) {
      $tempAttributes[] = "{$attribute}=\"{$value}\"";
    }
    $attributes = implode(' ', $tempAttributes);

    // @TODO: add # or other hosts?
    if($href != '')  { $href  = "href=\"?{$href}\"";  }
    if($css != '')   { $css   = "class=\"{$css}\"";   }
    if($title != '') { $title = "title=\"{$title}\""; }

    // @TODO escaping and urlencoding?
    return "<a {$href} {$title} {$css} {$attributes}>{$content}</a>";
  }

  /**
   * @inheritDoc
   */
  public function __construct(array $urlParams, $content = '', $title = '', array $cssClasses = array(), array $attributes = array())
  {
    $attributes['title'] = $title;

    // temporary workaround for changing templates when clicking on a link.
    if(\codename\core\app::getRequest()->getData('template') == 'coreadminempty') {
      $urlParams['template'] = 'coreadminempty';
    }

    $configArray = array(
      'params' => $urlParams,
      'css' => $cssClasses,
      'attributes' => $attributes,
      'content' => $content
    );
    $value = parent::__construct($configArray);
    return $value;
  }

  /**
   * @return \codename\core\frontend\link
   */
  public static function create(array $urlParams, $content = '', $title = '', array $cssClasses = array(), array $attributes = array()) : element {
    $link = new self($urlParams,$content,$title,$cssClasses,$attributes);
    return $link;
  }

}
