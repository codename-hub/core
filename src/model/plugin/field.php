<?php
namespace codename\core\model\plugin;

/**
 * Request a single field or more fields
 * @package core
 * @since 2016-02-04
 */
class field extends \codename\core\model\plugin implements \codename\core\model\plugin\field\fieldInterface {

    /**
     * Contains the $field to return
     * @var \codename\core\value\text\modelfield $field
     */
    public $field = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__CONSTRUCT(string $field)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field) {
        $this->field = $field;
        return $this;
    }
    
}
