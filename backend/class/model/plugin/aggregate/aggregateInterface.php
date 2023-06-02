<?php

namespace codename\core\model\plugin\aggregate;

use codename\core\value\text\modelfield;

/**
 * Definition for \codename\core\model\plugin\calculation
 * @package core
 * @since 2017-05-18
 */
interface aggregateInterface
{
    /**
     * [__construct description]
     * @param modelfield $field [description]
     * @param string $calculationType [description]
     * @param modelfield $fieldBase [description]
     */
    public function __construct(modelfield $field, string $calculationType, modelfield $fieldBase);

    /**
     * returns the appropriate query string for the respective database type
     * based on the settings provided in this object
     * you can provide a tableAlias on need (for handling ambiguous field names)
     *
     * @param string|null $tableAlias [description]
     * @return string             [description]
     */
    public function get(string $tableAlias = null): string;
}
