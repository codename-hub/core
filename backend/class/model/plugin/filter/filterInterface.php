<?php

namespace codename\core\model\plugin\filter;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\filter
 * @package core
 * @since 2016-02-04
 */
interface filterInterface
{
    /**
     * Creates the filter plugin and sets the important data ($field and $value)
     * call the method and pass the $field and the $value to it.
     * you can also add the $operator
     * @param modelfield $field
     * @param mixed $value
     * @param string $operator
     */
    public function __construct(modelfield $field, mixed $value, string $operator);

    /**
     * returns the field specifier, optionally using a given table alias
     * @param string|null $tableAlias [description]
     * @return string        [description]
     */
    public function getFieldValue(?string $tableAlias = null): string;
}
