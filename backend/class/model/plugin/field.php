<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\field\fieldInterface;
use codename\core\value\text\modelfield;

/**
 * Request a single field or more fields
 * @package core
 * @since 2016-02-04
 */
class field extends plugin implements fieldInterface
{
    /**
     * Contains the $field to return
     * @var null|modelfield $field
     */
    public ?modelfield $field = null;

    /**
     * [public description]
     * @var null|modelfield
     */
    public ?modelfield $alias = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__construct(string $field)
     */
    public function __construct(modelfield $field, ?modelfield $alias = null)
    {
        $this->field = $field;
        $this->alias = $alias;
        return $this;
    }
}
