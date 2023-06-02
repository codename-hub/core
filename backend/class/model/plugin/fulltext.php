<?php

namespace codename\core\model\plugin;

use codename\core\exception;
use codename\core\model\plugin;
use codename\core\model\plugin\fulltext\fulltextInterface;
use codename\core\value\text\modelfield;

/**
 * Plugin for creating fulltext fields
 * @package core
 * @author Ralf Thieme
 * @since 2019-03-04
 */
abstract class fulltext extends plugin implements fulltextInterface
{
    /**
     * [public description]
     * @var null|modelfield
     */
    public ?modelfield $field = null;

    /**
     * [public description]
     * @var array
     */
    public array $fields = [];

    /**
     * [public description]
     * @var mixed
     */
    public mixed $value = '';

    /**
     * [__construct description]
     * @param modelfield $field [description]
     * @param string $value [description]
     * @param array $fields [description]
     * @throws exception
     */
    public function __construct(modelfield $field, mixed $value, array $fields)
    {
        foreach ($fields as $thisfield) {
            if (!$thisfield instanceof modelfield) {
                throw new exception('EXCEPTION_MODEL_PLUGIN_FULLTEXT_BAD_FIELD', exception::$ERRORLEVEL_FATAL, $thisfield);
            }
        }
        $this->field = $field;
        $this->fields = $fields;
        $this->value = $value;
        return $this;
    }
}
