<?php
namespace codename\core\model\plugin;

/**
 * Tell the model to limit the results
 * @package core
 * @since 2016-02-04
 */
class limit extends \codename\core\model\plugin implements \codename\core\model\plugin\limit\limitInterface {

    /**
     * $limit of datarows to write to the result
     * @var integer $limit
     */
    public $limit = null;
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_limit::__CONSTRUCT(integer $offset)
     */
    public function __CONSTRUCT(int $limit) {
        $this->limit = $limit;
        return $this;
    }
    
}
