<?php
namespace codename\core\model\plugin\order;

/**
 * Definition for \codename\core\model\plugin\order
 * @package core
 * @since 2016-02-04
 */
interface orderInterface {
    
    /**
     * Creates the order plugin and sets the important data ($field and $direction)
     * @param \codename\core\value\text\modelfield $field
     * @param string $direction
     * @return \codename\core\model_plugin_order
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $direction);
    
}
