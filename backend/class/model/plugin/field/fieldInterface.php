<?php

namespace codename\core\model\plugin\field;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\field
 * @package core
 * @since 2016-02-04
 */
interface fieldInterface
{
    /**
     * Sets the field for this instance and returns the instance
     * Use it to add fields to a model request
     *
     * @param modelfield $field [description]
     * @param modelfield|null $alias [description]
     */
    public function __construct(modelfield $field, ?modelfield $alias);
}
