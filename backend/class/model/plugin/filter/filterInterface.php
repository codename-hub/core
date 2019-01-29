<?php
namespace codename\core\model\plugin\filter;

/**
 * Definition for \codename\core\model\plugin\filter
 * @package core
 * @since 2016-02-04
 */
interface filterInterface {

    /**
     * Creates the filter plugin and sets the important data ($field and $value)
     * <br />call the method and pass the $field and the $value to it.
     * <br />you can also add the $operator
     * @param \codename\core\value\text\modelfield $field
     * @param string $value
     * @param string $operator
     * @return \codename\core\model_plugin_filter
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value = null, string $operator);

    /**
     * returns the field specifier, optionally using a given table alias
     * @param  string|null   $tableAlias [description]
     * @return string        [description]
     */
    public function getFieldValue(string $tableAlias = null) : string;
}
