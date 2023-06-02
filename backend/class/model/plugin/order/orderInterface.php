<?php

namespace codename\core\model\plugin\order;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\order
 * @package core
 * @since 2016-02-04
 */
interface orderInterface
{
    /**
     * Creates the order plugin and sets the important data ($field and $direction)
     * @param modelfield $field
     * @param string $direction
     */
    public function __construct(modelfield $field, string $direction);
}
