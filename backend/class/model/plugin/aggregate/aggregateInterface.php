<?php
namespace codename\core\model\plugin\aggregate;

/**
 * Definition for \codename\core\model\plugin\calculation
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
interface aggregateInterface {


    /**
     * [__construct description]
     * @param \codename\core\value\text\modelfield  $field           [description]
     * @param string                                $calculationType [description]
     * @param \codename\core\value\text\modelfield  $fieldBase       [description]
     */
    public function __construct(\codename\core\value\text\modelfield $field, string $calculationType, \codename\core\value\text\modelfield $fieldBase);

    /**
     * returns the appropriate query string for the respective database type
     * based on the settings provided in this object
     * you can provide a tableAlias on need (for handling ambiguous field names)
     *
     * @param  string|null $tableAlias [description]
     * @return string             [description]
     */
    public function get(string $tableAlias = null) : string;
}
