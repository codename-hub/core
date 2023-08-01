<?php

namespace codename\core\frontend;

use codename\core\app;

/**
 * Link creation/link class
 * @package core
 * @since 2017-01-05
 */
class link extends element
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $urlParams, $content = '', $title = '', array $cssClasses = [], array $attributes = [])
    {
        $attributes['title'] = $title;

        // temporary workaround for changing templates when clicking on a link.
        if (app::getRequest()->getData('template') == 'coreadminempty') {
            $urlParams['template'] = 'coreadminempty';
        }

        $configArray = [
          'params' => $urlParams,
          'css' => $cssClasses,
          'attributes' => $attributes,
          'content' => $content,
        ];
        return parent::__construct($configArray);
    }

    /**
     * @param array $urlParams
     * @param string $content
     * @param string $title
     * @param array $cssClasses
     * @param array $attributes
     * @return element
     */
    public static function create(array $urlParams, string $content = '', string $title = '', array $cssClasses = [], array $attributes = []): element
    {
        return new self($urlParams, $content, $title, $cssClasses, $attributes);
    }

    /**
     * @return string
     */
    public function output(): string
    {
        $href = http_build_query($this->config->get('params'));
        $css = implode(' ', $this->config->get('css'));
        $content = $this->config->get('content');
        $title = $this->config->get('title');

        $tempAttributes = [];
        foreach ($this->config->get('attributes') as $attribute => $value) {
            $tempAttributes[] = "$attribute=\"$value\"";
        }
        $attributes = implode(' ', $tempAttributes);

        // @TODO: add # or other hosts?
        if ($href != '') {
            $href = "href=\"?$href\"";
        }
        if ($css != '') {
            $css = "class=\"$css\"";
        }
        if ($title != '') {
            $title = "title=\"$title\"";
        }

        // @TODO escaping and urlencoding?
        return "<a $href $title $css $attributes>$content</a>";
    }
}
