<?php
namespace codename\core;

/**
 * The main database class that uses the php pdo-object for SQL database interaction
 * @package core
 * @since 2016-01-06
 */
class database extends \codename\core\observable {

    /**
     * It seems there's a problem when connecting to the desired database server.
     * <br />The server may be offline, misconfigured or your configuration is wrong.
     * @var string
     */
    CONST EXCEPTION_CONSTRUCT_CONNECTIONERROR = 'EXCEPTION_CONSTRUCT_CONNECTIONERROR';

    /**
     * The query that was being executed id not finis correctly.
     * <br />It may contain errors
     * @var string
     */
    CONST EXCEPTION_QUERY_QUERYERROR = 'EXCEPTION_QUERY_QUERYERROR';

    /**
     * Contains the current driver name
     * @var string
     */
    public $driver = null;

    /**
     * Contains the \PDO instance of this DB instance
     * @var \PDO
     */
    protected $connection = null;

    /**
     * Contains the \PDOStatement instance of this DB instance after performing a query
     * @var \PDOStatement
     */
    protected $statement = null;

    /**
     * log name for queries
     * null to disable
     * @var string|null
     */
    protected $queryLog = null;

    /**
     * Creates an instance with the given data
     * @param array $config
     * @return \codename\core\database
     */
    public function __construct(array $config) {
        try {
            if (isset($config['env_pass'])) {
              $pass = getenv($config['env_pass']);
            } else if(isset($config['pass'])) {
              $pass = $config['pass'];
            } else {
              throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, array('ENV_PASS_NOT_SET'));
            }

            // set query log
            $this->queryLog = $config['querylog'] ?? null;

            // allow connections without database name
            // just put in autoconnect_database = false
            $autoconnectDatabase = true;
            if(isset($config['autoconnect_database'])) {
              $autoconnectDatabase = $config['autoconnect_database'];
            }

            $this->connection = new \PDO($this->driver . ":" . ( $autoconnectDatabase ? "dbname=" . $config['database'] . ";" : '') . 'host=' . $config['host'] . (isset($config['charset']) ? (';charset='.$config['charset']) : ''), $config['user'], $pass);

            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        }
        catch (\PDOException $e) {
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, $e);
        }

        $this->attach(new \codename\core\observer\database());
        return $this;
    }

    /**
     * Performs the given $query on the \PDO instance.
     * <br />Stores the \PDOStatement to the instance for result management
     * @param string $query
     * @param array $params
     * @return void
     */
    public function query (string $query, array $params = array()) {
      if($this->queryLog) {
        app::getLog($this->queryLog)->debug($query);
      }
      app::getHook()->fire(\codename\core\hook::EVENT_DATABASE_QUERY_QUERY_BEFORE, array('query' => $query, 'params' => $params));
      $this->statement = $this->connection->prepare($query);

      foreach($params as $key => $param) {
        // use parameters set in getParametrizedValue
        // 0 => value, 1 => \PDO::PARAM_...
        $this->statement->bindValue($key, $param[0], $param[1]);
      }

      $res = $this->statement->execute();

      // explicitly check for falseness identity, not only == (equality), which may evaluate a 0 to a false.
      if ($res === false) {
        throw new \codename\core\exception(self::EXCEPTION_QUERY_QUERYERROR, \codename\core\exception::$ERRORLEVEL_FATAL, array('errors' => $this->statement->errorInfo(), 'query' => $query, 'params' => $params));
      }

      app::getHook()->fire(\codename\core\hook::EVENT_DATABASE_QUERY_QUERY_AFTER);
      $this->notify();
      return;
    }

    /**
     * Returns the array of records in the result
     * @return array
     */
    public function getResult() : array {
        if(is_null($this->statement)) {
            return array();
        }
        return $this->statement->fetchAll(\PDO::FETCH_NAMED);
    }

    /**
     * Returns the last insterted ID for the past query
     * @return string
     */
    public function lastInsertId() : string {
        return $this->connection->lastInsertId();
    }

    /**
     * [getConnection description]
     * @return \PDO [description]
     */
    public function getConnection() : \PDO {
      return $this->connection;
    }

}
