<?php
namespace codename\core\model\plugin;

/**
 * Group plugin for GROUP BY queries
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
class group extends \codename\core\model\plugin implements \codename\core\model\plugin\group\groupInterface {

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
