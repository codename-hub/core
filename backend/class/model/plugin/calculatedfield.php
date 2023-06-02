<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\calculatedfield\calculatedfieldInterface;
use codename\core\value\text\modelfield;

/**
 * Plugin for creating calculated fields and their alias
 * @package core
 * @since 2017-05-18
 */
abstract class calculatedfield extends plugin implements calculatedfieldInterface
{
    /**
     * Contains the $field to return
     * @var null|modelfield $field
     */
    public ?modelfield $field = null;

    /**
     * contains the SQL query part where we construct our calculation
     * @var null|string
     */
    public ?string $calculation = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__construct(string $field)
     */
    public function __construct(modelfield $field, string $calculation)
    {
        $this->field = $field;
        $this->calculation = $calculation;
        return $this;
    }
}
