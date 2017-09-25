<?php
namespace codename\core\model\plugin\offset;

/**
 * Definition for \codename\core\model\plugin\offset
 * @package core
 * @since 2016-02-04
 */
interface offsetInterface {

    /**
     * Creates the offset plugin and sets the important data ($offset)
     * @param integer $offset
     * @return \codename\core\model_plugin_offset
     */
    public function __CONSTRUCT(int $offset);
    
}
