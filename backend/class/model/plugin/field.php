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
     * [public description]
     * @var \codename\core\value\text\modelfield
     */
    public $alias = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__CONSTRUCT(string $field)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, ?\codename\core\value\text\modelfield $alias = null) {
        $this->field = $field;
        $this->alias = $alias;
        return $this;
    }

}
