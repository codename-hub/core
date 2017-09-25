<?php
namespace codename\core\model\plugin\calculatedfield;

/**
 * Definition for \codename\core\model\plugin\calculatedfield
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
interface calculatedfieldInterface {


    /**
     * Sets the field for this instance and returns the instance
     * <br />Use it to add fields to a model request
     * @param \codename\core\value\text\modelfield $field
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $calculation);

    /**
     * returns the appropriate query string for the respective database type
     * based on the settings provided in this object
     * @return string
     */
    public function get() : string;
}
