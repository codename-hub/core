<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\order\orderInterface;
use codename\core\value\text\modelfield;

/**
 * Tell the model to order the results
 * @package core
 * @since 2016-02-04
 */
class order extends plugin implements orderInterface
{
    /**
     * Contains the $field to order
     * @var null|modelfield $field
     */
    public ?modelfield $field = null;

    /**
     * Contains the direction for the ordering
     * @var string $direction
     */
    public string $direction;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_order_interface::__construct(string $field, string $direction)
     */
    public function __construct(modelfield $field, string $direction)
    {
        $this->field = $field;
        $this->direction = $direction;
        return $this;
    }
}
