<?php

namespace codename\core\model\plugin\fulltext;

use codename\core\value\text\modelfield;

/**
 * @package core
 * @author Ralf Thieme
 * @since 2019-03-04
 */
interface fulltextInterface
{
    /**
     * [__construct description]
     * @param modelfield $field [description]
     * @param mixed $value [description]
     * @param array $fields [description]
     */
    public function __construct(modelfield $field, mixed $value, array $fields);

    /**
     * returns the appropriate query string for the respective database type
     * based on the settings provided in this object
     * you can provide a tableAlias on need (for handling ambiguous field names)
     *
     * this function needs to get the model instance
     * to use some preparation functions while generating query code
     *
     * @param string $value
     * @param string|null $tableAlias [description]
     * @return string                       [description]
     */
    public function get(string $value, string $tableAlias = null): string;

    /**
     * returns the value to be used for fulltext searching
     * @return string [description]
     */
    public function getValue(): string;

    /**
     * output field to use
     * @return string [description]
     */
    public function getField(): string;
}
