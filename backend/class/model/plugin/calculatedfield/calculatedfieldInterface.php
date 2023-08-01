<?php

namespace codename\core\model\plugin\calculatedfield;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\calculatedfield
 * @package core
 * @since 2017-05-18
 */
interface calculatedfieldInterface
{
    /**
     * Sets the field for this instance and returns the instance
     * Use it to add fields to a model request
     * @param modelfield $field
     * @param string $calculation
     */
    public function __construct(modelfield $field, string $calculation);

    /**
     * returns the appropriate query string for the respective database type
     * based on the settings provided in this object
     * @return string
     */
    public function get(): string;
}
