<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\aggregate\aggregateInterface;
use codename\core\value\text\modelfield;

/**
 * Plugin for creating calculation (aggregate function) fields by types and their alias
 * @package core
 * @since 2017-05-18
 */
abstract class aggregate extends plugin implements aggregateInterface
{
    /**
     * Contains the $field to return
     * @var modelfield $field
     */
    public modelfield $field;

    /**
     * contains a known type of calculation
     * @var string
     */
    public string $calculationType;

    /**
     * field the calculation relies on
     * @var modelfield
     */
    public modelfield $fieldBase;

    /**
     * [__construct description]
     * @param modelfield $field [description]
     * @param string $calculationType [description]
     * @param modelfield $fieldBase [description]
     */
    public function __construct(modelfield $field, string $calculationType, modelfield $fieldBase)
    {
        $this->field = $field;
        $this->fieldBase = $fieldBase;
        $this->calculationType = $calculationType;
        return $this;
    }
}
