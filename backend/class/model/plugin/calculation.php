<?php
namespace codename\core\model\plugin;

/**
 * Plugin for creating calculation fields by types and their alias
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
abstract class calculation extends \codename\core\model\plugin implements \codename\core\model\plugin\calculation\calculationInterface {

    /**
     * Contains the $field to return
     * @var \codename\core\value\text\modelfield $field
     */
    public $field = null;

    /**
     * contains a known type of calculation
     * @var string
     */
    public $calculationType = null;

    /**
     * field the calculation relies on
     * @var \codename\core\value\text\modelfield
     */
    public $fieldBase = null;

    /**
     * [__CONSTRUCT description]
     * @param  \codename\core\value\text\modelfield $field           [description]
     * @param  string                          $calculationType [description]
     * @param  \codename\core\value\text\modelfield $fieldBase       [description]
     * @return [type]                                           [description]
     */
    public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $calculationType, \codename\core\value\text\modelfield $fieldBase) {
        $this->field = $field;
        $this->fieldBase = $fieldBase;
        $this->calculationType = $calculationType;
        return $this;
    }

}
