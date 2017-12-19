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

    /**
     * Contains more models
     * @var unknown $tables
     */
    protected $tables = array();

    /**
     * resets all the parameters of the instance for another query
     * @return void
     */
    public function reset() {
        parent::reset();
        $this->tables = array();
        return;
    }

}
