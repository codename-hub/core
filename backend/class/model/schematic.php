<?php
namespace codename\core\model;

/**
 * Handling schematic storages (SQL)
 * @package core
 * @since 2016-02-04
 */
abstract class schematic extends \codename\core\model {

    /**
     * Contains the schema this model is based upon
     * @var string
     */
    public $schema = null;

    /**
     * Contains the table this model is based upon
     * @var string
     */
    public $table = null;

}
