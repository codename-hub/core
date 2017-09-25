<?php
namespace codename\core\model\plugin\field;

/**
 * Definition for \codename\core\model\plugin\field
 * @package core
 * @since 2016-02-04
 */
interface fieldInterface {


    /**
     * Sets the field for this instance and returns the instance
     * <br />Use it to add fields to a model request
     * @param \codename\core\value\text\modelfield $field
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field);
    
}
