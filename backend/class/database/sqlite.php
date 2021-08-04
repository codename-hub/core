<?php
namespace codename\core\database;

use codename\core\sensitiveException;

/**
 * SQLite db driver
 * @package core
 * @author Kevin Dargel
 * @since 2020-01-03
 */
class sqlite extends \codename\core\database {

    /**
     * Contains the driver name
     * @var string
     */
    public $driver = 'sqlite';

    /**
     * @inheritDoc
     */
    public function __construct(array $config)
    {
      try {
          // set query log
          $this->queryLog = $config['querylog'] ?? null;

          // allow connections without database name
          // just put in autoconnect_database = false
          $autoconnectDatabase = true;
          if(isset($config['autoconnect_database'])) {
            $autoconnectDatabase = $config['autoconnect_database'];
          }

          if($config['emulation_mode'] ?? false) {
            $this->emulationMode = true;
          }

          try {
            $file = $config['database_file'];
            if($config['database_file_path_relative'] ?? false) {
              $file = \codename\core\app::getHomedir($config['database_home']['vendor'], $config['database_home']['app']).'/'.$file;
            }
            $this->connection = new \PDO($this->driver . ":" . $file);
          } catch (\Exception $e) {
            throw new sensitiveException($e);
          }

          $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
          $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

          $this->connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [\codename\core\extendedPdoStatement::class, [ $this->connection ] ]);
      }
      catch (\PDOException $e) {
          throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, $e);
      }

      $this->attach(new \codename\core\observer\database());
      return $this;
      //
      // parent::__construct($config);
      // // if(isset($config['charset'])) {
      // //   $exec = $this->connection->exec('SET NAMES ' . $config['charset'] . '; CHARACTER SET '.$config['charset'].';');
      // // }
      // return $this;
    }

    /**
     * @inheritDoc
     */
    public function query(string $query, array $params = array())
    {
      if($this->emulationMode) {
        // $query = preg_replace('/([A-Z_a-z0-9]+\.[A-Z_a-z0-9]+)/', '"$1"', $query);
        $query = str_ireplace('NOW()', "strftime('%Y-%m-%d %H:%M:%S','now')", $query);
      }
      // $query = preg_replace('/(d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', 'strftime(\'%s\', \'$1\')', $query);
      $this->sqliteQueryLog[] = $query;
      return parent::query($query, $params);
    }

    /**
     * [protected description]
     * @var [type]
     */
    protected $sqliteQueryLog = [];

    /**
     * [getQueryLog description]
     * @return array [description]
     */
    public function getQueryLog() : array {
      return $this->sqliteQueryLog;
    }

    /**
     * [protected description]
     * @var bool
     */
    protected $emulationMode = false;

}
