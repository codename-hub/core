<?php
namespace codename\core\database;

/**
 * MySQL/MariaDB db driver
 * @package core
 * @author Kevin Dargel
 * @since 2017-03-01
 */
class mysql extends \codename\core\database {

    /**
     * Contains the driver name
     * @var string
     */
    public $driver = 'mysql';

    /**
     * @inheritDoc
     */
    public function __construct(array $config)
    {
      parent::__construct($config);
      if(isset($config['charset'])) {
        $exec = $this->connection->exec('SET NAMES ' . $config['charset'] . '; CHARACTER SET '.$config['charset'].';');
      }
      return $this;
    }

}
