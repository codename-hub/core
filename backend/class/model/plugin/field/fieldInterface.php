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
     * Use it to add fields to a model request
     *
     * @param  \codename\core\value\text\modelfield       $field [description]
     * @param  \codename\core\value\text\modelfield|null  $alias [description]
     * @return [type]                                  [description]
     */
    public function __construct(\codename\core\value\text\modelfield $field, ?\codename\core\value\text\modelfield $alias);

}
