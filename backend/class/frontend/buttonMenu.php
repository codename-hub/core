<?php

namespace codename\core\frontend;

/**
 * Menu Button Link creation/link class
 * @package core
 * @since 2017-01-05
 */
class buttonMenu extends element
{
    /**
     * array of menu elements
     * @var element[]
     */
    public array $items = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $items, $iconCss = '', $title = '', array $cssClasses = [], array $attributes = [])
    {
        $attributes['title'] = $title;
        $this->items = $items;
        $configArray = [
          'title' => $title,
          'css' => $cssClasses,
          'attributes' => $attributes,
        ];
        return parent::__construct($configArray);
    }

    /**
     * @param array $items
     * @param string $iconCss
     * @param string $title
     * @param array $cssClasses
     * @param array $attributes
     * @return string
     */
    public static function getHtml(array $items, string $iconCss = '', string $title = '', array $cssClasses = [], array $attributes = []): string
    {
        $link = new self($items, $iconCss, $title, $cssClasses, $attributes);
        return $link->output();
    }

    /**
     * @return string
     */
    public function output(): string
    {
        $title = $this->config->get('title');
        $html = "<button type=\"button\" class=\"btn btn-xs btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
  	 <i class=\"icon icon-cogs\"></i> $title <span class=\"caret\"></span>
    </button>
    <ul class=\"dropdown-menu\">";
        foreach ($this->items as $item) {
            $html .= "<li>" . $item->output() . "</li>";
        }
        $html .= "</ul>";
        return $html;
    }

    /**
     * @param array $items
     * @param string $iconCss
     * @param string $title
     * @param array $cssClasses
     * @param array $attributes
     * @return element
     */
    public static function create(array $items, string $iconCss = '', string $title = '', array $cssClasses = [], array $attributes = []): element
    {
        return new self($items, $iconCss, $title, $cssClasses, $attributes);
    }

    /**
     * @param element $element
     * @return void
     */
    public function addItem(element $element): void
    {
        $this->items[] = $element;
    }
}
