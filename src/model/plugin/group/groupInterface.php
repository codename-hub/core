<?php
namespace codename\core\model\plugin\group;

/**
 * Definition for \codename\core\model\plugin\group
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
interface groupInterface {


    /**
     * Sets the field for this instance and returns the instance
     * <br />Use it to add fields to a model request
     * @param \codename\core\value\text\modelfield $field
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field);

}
