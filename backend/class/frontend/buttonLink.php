<?php

namespace codename\core\frontend;

/**
 * Button Link creation/link class, extended
 * @package core
 * @since 2017-01-05
 */
class buttonLink extends link
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $urlParams, string $content = '', string $title = '', array $cssClasses = [], array $attributes = [], string $iconCss = '')
    {
        $content = "<span class=\"$iconCss\"></span>" . ($content != '' ? '&nbsp;' . $content : '');
        return parent::__construct($urlParams, $content, $title, $cssClasses, $attributes);
    }

    /**
     * @param array $urlParams
     * @param string $content
     * @param string $title
     * @param array $cssClasses
     * @param array $attributes
     * @param string $iconCss
     * @return string
     */
    public static function getHtml(array $urlParams, string $content = '', string $title = '', array $cssClasses = [], array $attributes = [], string $iconCss = ''): string
    {
        $link = new self($urlParams, $content, $title, $cssClasses, $attributes, $iconCss);
        return $link->output();
    }

    /**
     * @param array $urlParams
     * @param string $content
     * @param string $title
     * @param array $cssClasses
     * @param array $attributes
     * @param string $iconCss
     * @return element
     */
    public static function create(array $urlParams, string $content = '', string $title = '', array $cssClasses = [], array $attributes = [], string $iconCss = ''): element
    {
        return new self($urlParams, $content, $title, $cssClasses, $attributes, $iconCss);
    }
}
