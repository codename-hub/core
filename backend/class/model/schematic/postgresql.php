<?php
namespace codename\core\model\schematic;
use \codename\core\app;

/**
 * postgreSQL's specific SQL commands
 * @package core
 * @since 2016-02-04
 */
abstract class postgresql extends \codename\core\model\schematic\sql implements \codename\core\model\modelInterface {

    /**
     * @todo DOCUMENTATION
     */
    CONST DB_TYPE = 'postgresql';
    
}
