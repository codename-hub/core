<?php

namespace codename\core\frontend;

/**
 * Button Group creation/link class
 * @package core
 * @since 2017-01-05
 */
class buttonGroup extends element
{
    /**
     * array of menu elements
     * @var element[]
     */
    public array $items = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $items)
    {
        $this->items = $items;
        $configArray = [];
        return parent::__construct($configArray);
    }

    /**
     * @param array $items
     * @return string
     */
    public static function getHtml(array $items): string
    {
        $element = new self($items);
        return $element->output();
    }

    /**
     * @return string
     */
    public function output(): string
    {
        $html = "<div class=\"btn-group\">";
        foreach ($this->items as $item) {
            $html .= $item->output();
        }
        $html .= "</div>";
        return $html;
    }

    /**
     * @param array $items
     * @return element
     */
    public static function create(array $items): element
    {
        return new self($items);
    }

    /**
     * @param element $ele
     * @return void
     */
    public function addItem(element $ele): void
    {
        $this->items[] = $ele;
    }
}
