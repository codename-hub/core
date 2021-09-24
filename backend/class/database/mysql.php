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
      //
      // We don't need to set this explicitly as of PHP8
      // errors seem to have been gobbled up in the past.
      //
      // if(isset($config['charset'])) {
      //   $exec = $this->connection->exec('SET NAMES ' . $config['charset'] . '; CHARACTER SET '.$config['charset'].';');
      // }
      return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultAttributes(): array
    {
      return [
        // CHANGED 2021-05-04: this fixes invalid rowcount
        // on UPDATE where nothing really changed
        \PDO::MYSQL_ATTR_FOUND_ROWS => true,
      ];
    }

}
