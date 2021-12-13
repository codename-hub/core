<?php
namespace codename\core\model\plugin\filterlist;

/**
 * Definition for \codename\core\model\plugin\filterlist
 * @package core
 * @since 2016-02-04
 */
interface filterlistInterface {

    /**
     * Creates the filterlist plugin and sets the important data ($field and $value)
     * <br />call the method and pass the $field and the $value to it.
     * <br />you can also add the $operator
     * @param \codename\core\value\text\modelfield $field
     * @param string $value
     * @param string $operator
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, $value = null, string $operator);

    /**
     * returns the field specifier, optionally using a given table alias
     * @param  string|null   $tableAlias [description]
     * @return string        [description]
     */
    public function getFieldValue(string $tableAlias = null) : string;
}
