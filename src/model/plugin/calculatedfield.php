<?php
namespace codename\core\model\plugin;

/**
 * Plugin for creating calculated fields and their alias
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
abstract class calculatedfield extends \codename\core\model\plugin implements \codename\core\model\plugin\calculatedfield\calculatedfieldInterface {

    /**
     * Contains the $field to return
     * @var \codename\core\value\text\modelfield $field
     */
    public $field = null;

    /**
     * contains the SQL query part where we construct our calculation
     * @var string
     */
    public $calculation = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_field_interface::__CONSTRUCT(string $field)
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $calculation) {
        $this->field = $field;
        $this->calculation = $calculation;
        return $this;
    }

}
