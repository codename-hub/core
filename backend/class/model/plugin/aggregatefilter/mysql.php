<?php

namespace codename\core\model\plugin\aggregatefilter;

use codename\core\model\plugin\aggregatefilter;
use codename\core\model\plugin\filter\filterInterface;
use codename\core\value\text\modelfield;

/**
 * Tell a model to filter aggregate field values
 * @package core
 * @since 2017-03-01
 */
class mysql extends aggregatefilter implements filterInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(modelfield $field, mixed $value, string $operator)
    {
        parent::__construct($field, $value, $operator);
        return $this;
    }
}
