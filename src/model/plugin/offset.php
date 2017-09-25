<?php
namespace codename\core\model\plugin;

/**
 * Tell the model to offset the results
 * @package core
 * @since 2016-02-04
 */
class offset extends \codename\core\model\plugin implements \codename\core\model\plugin\offset\offsetInterface {

    /**
     * $count of datarows to offset the result
     * @var integer $offset
     */
    public $offset = null;
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_offset::__CONSTRUCT(integer $offset)
     */
    public function __CONSTRUCT(int $offset) {
        $this->offset = $offset;
        return $this;
    }
    
}
