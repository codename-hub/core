<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\group\groupInterface;
use codename\core\value\text\modelfield;

/**
 * Group plugin for GROUP BY queries
 * @package core
 * @since 2017-05-18
 */
class group extends plugin implements groupInterface
{
    /**
     * Contains the $field to return
     * @var null|modelfield $field
     */
    public ?modelfield $field = null;

    /**
     * whether this plugin is subject to dynamic table aliasing     *  === false : normal behaviour, may get a dynamic alias
     * aliased === true : respective field MAY NOT be used in dynamic aliasing
     * @var bool
     */
    public bool $aliased = false;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__construct(string $field)
     */
    public function __construct(modelfield $field)
    {
        $this->field = $field;
        return $this;
    }
}
