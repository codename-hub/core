<?php

namespace codename\core\model\plugin\group;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\group
 * @package core
 * @since 2017-05-18
 */
interface groupInterface
{
    /**
     * Sets the field for this instance and returns the instance
     * Use it to add fields to a model request
     * @param modelfield $field
     */
    public function __construct(modelfield $field);
}
