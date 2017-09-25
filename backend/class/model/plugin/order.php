<?php
namespace codename\core\model\plugin;

/**
 * Tell the model to order the results
 * @package core
 * @since 2016-02-04
 */
class order extends \codename\core\model\plugin implements \codename\core\model\plugin\order\orderInterface {

    /**
     * Contains the $field to order
     * @var \codename\core\value\text\modelfield $field
     */
    public $field = null;
    
    /**
     * Contains the direction for the ordering
     * @var string $direction
     */
    public $direction = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_order_interface::__CONSTRUCT(string $field, string $direction)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $direction) {
        $this->field = $field;
        $this->direction = $direction;
        return $this;
    }
    
}
