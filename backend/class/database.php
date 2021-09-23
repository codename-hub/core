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

            if (isset($config['env_host'])) {
              $host = getenv($config['env_host']);
            } else if(isset($config['host'])) {
              $host = $config['host'];
            } else {
              throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, array('ENV_HOST_NOT_SET'));
            }

            if (isset($config['env_user'])) {
              $user = getenv($config['env_user']);
            } else if(isset($config['user'])) {
              $user = $config['user'];
            } else {
              throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, array('ENV_USER_NOT_SET'));
            }

            // set query log
            $this->queryLog = $config['querylog'] ?? null;

            // allow connections without database name
            // just put in autoconnect_database = false
            $autoconnectDatabase = true;
            if(isset($config['autoconnect_database'])) {
              $autoconnectDatabase = $config['autoconnect_database'];
            }

            try {
              // CHANGED 2021-05-04: allow driver-specific default attrs, if any.
              $attrs = $this->getDefaultAttributes();
              $this->connection = new \PDO($this->driver . ":" . ( $autoconnectDatabase ? "dbname=" . $config['database'] . ";" : '') . 'host=' . $host . (isset($config['port']) ? (';port='.$config['port']) : '') . (isset($config['charset']) ? (';charset='.$config['charset']) : ''), $user, $pass, $attrs);
            } catch (\Exception $e) {
              throw new sensitiveException($e);
            }

            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // if using NON-PERSISTENT connections, we override the statement class with one of our own
            // WARNING/CHANGED 2021-09-23: added ctor arg as reference (&)
            // as it prevents PHP's GC from collecting this properly
            // and this may cause connection keepalive trouble, when re-connecting to the same server
            // in the same process.
            $this->connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [\codename\core\extendedPdoStatement::class, [ &$this->connection ] ]);
        }
        catch (\PDOException $e) {
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, \codename\core\exception::$ERRORLEVEL_FATAL, $e);
        }

        $this->attach(new \codename\core\observer\database());
        return $this;
    }

    /**
     * [__destruct description]
     */
    public function __destruct() {
      //
      // NOTE: if experiencing problems with the GC/refcounting
      // you might optionally clear out any leftover references to the PDO instance:
      //
      // $this->connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [ \PDOStatement::class ]);
      // $this->statement = null;
      // $this->statements = [];

      //
      // WARNING/CHANGED 2021-09-23: added destructor removing PDO object ref
      // see note in this classes' constructor.
      //
      $this->connection = null;
    }

    /**
     * Default attributes to use during connection creation
     * as some attributes have no effect otherwise
     * @return array
     */
    protected function getDefaultAttributes(): array {
      return [];
    }

    /**
     * [protected description]
     * @var \PDOStatement[]
     */
    protected $statements = [];

    /**
     * holds data about the amount/count of cached statements
     * to avoid calls to count() as far as possible
     * @var int
     */
    protected $statementsCount = 0;

    /**
     * limit of how many PDO Prepared Statement Instances are kept for this database
     * @var int
     */
    protected $maximumCachedStatements = 100;

    /**
     * [protected description]
     * @var int
     */
    protected $maximumCachedOptimizedStatements = 50;

    // DEBUG Statement preparation performance
    // public $statementPreparedCount = 0;
    // public $statementsClearCount = 0;
    // public $statementReusageCount = 0;
    // public $statementUsageStatistics = [];

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

      // NOTE: disabled firing hooks for now.
      // app::getHook()->fire(\codename\core\hook::EVENT_DATABASE_QUERY_QUERY_BEFORE, array('query' => $query, 'params' => $params));

      $this->statement = null;
      foreach($this->statements as $statement) {
        if($statement->queryString == $query) {
          $this->statement = $statement;

          // DEBUG Statement preparation performance
          // $this->statementReusageCount++;
          // $this->statementUsageStatistics[$query]++;
          break;
        }
      }
      if($this->statement === null) {
        $this->statement = $this->connection->prepare($query);

        // DEBUG Statement preparation performance
        // $this->statementPreparedCount++;

        //
        // Clear cached prepared PDO statements, if there're more than N of them
        //
        if($this->statementsCount > $this->maximumCachedStatements) {
          if($this->maximumCachedOptimizedStatements) {
            uasort($this->statements, function(extendedPdoStatement $a, extendedPdoStatement $b) {
              return $a->getExecutionCount() <=> $b->getExecutionCount();
            });
            $this->statements = array_slice($this->statements, 0, $this->maximumCachedOptimizedStatements);
            $this->statementsCount = count($this->statements);
          } else {
            $this->statements = [];
            $this->statementsCount = 0;
          }

          // DEBUG Statement preparation performance
          // $this->statementsClearCount++;
        }

        $this->statements[] = $this->statement;
        $this->statementsCount++;
      }

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

      // NOTE: disabled firing hooks for now.
      // app::getHook()->fire(\codename\core\hook::EVENT_DATABASE_QUERY_QUERY_AFTER);
      // NOTE: disabled calling notify() (observer)
      // $this->notify();
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
     * Returns count of affected rows of last
     * create, update or delete operation
     * @return int|null
     */
    public function affectedRows(): ?int {
      if($this->statement) {
        return $this->statement->rowCount();
      } else {
        return null;
      }
    }

    /**
     * [getConnection description]
     * @return \PDO [description]
     */
    public function getConnection() : \PDO {
      return $this->connection;
    }

    /**
     * Virtual Transaction Counter
     * @var array
     */
    protected $virtualTransactions = [];

    /**
     * Global virtual transaction counter
     * @var int
     */
    protected $aggregatedVirtualTransactions = 0;

    /**
     * Starts a virtualized transaction
     * that may handle multi-model transactions
     *
     * @param  string $transactionName [description]
     * @return void
     */
    public function beginVirtualTransaction(string $transactionName = 'default') {
      if(!isset($this->virtualTransactions[$transactionName])) {
        $this->virtualTransactions[$transactionName] = 0;
      }
      if(($this->virtualTransactions[$transactionName] === 0) && ($this->aggregatedVirtualTransactions === 0)) {
        // this may cause errors when using multiple transaction names...
        if($this->connection->inTransaction()) {
          throw new exception('EXCEPTION_DATABASE_VIRTUALTRANSACTION_UNTRACKED_TRANSACTION_RUNNING', exception::$ERRORLEVEL_FATAL);
        }
        // We have no open transactions with the given name, open a new one
        $this->connection->beginTransaction();
      }

      $this->virtualTransactions[$transactionName]++;
      $this->aggregatedVirtualTransactions++;
    }

    /**
     * [endVirtualTransaction description]
     * @param  string $transactionName [description]
     * @return [type]                  [description]
     */
    public function endVirtualTransaction(string $transactionName = 'default') {
      if(!isset($this->virtualTransactions[$transactionName]) || ($this->virtualTransactions[$transactionName] === 0)) {
        throw new exception('EXCEPTION_DATABASE_VIRTUALTRANSACTION_END_DOESNOTEXIST', exception::$ERRORLEVEL_FATAL, $transactionName);
      }
      if(!$this->connection->inTransaction()) {
        throw new exception('EXCEPTION_DATABASE_VIRTUALTRANSACTION_END_TRANSACTION_INTERRUPTED', exception::$ERRORLEVEL_FATAL, $transactionName);
      }

      $this->virtualTransactions[$transactionName]--;
      $this->aggregatedVirtualTransactions--;

      if(($this->virtualTransactions[$transactionName] === 0) && ($this->aggregatedVirtualTransactions === 0)) {
        $this->connection->commit();
      }
    }

    /**
     * performs a full rollback of currently pending transactions
     * NOTE: this kills _all_ applicable transactions.
     * @return void
     */
    public function rollback() {
      $this->virtualTransactions = [];
      $this->aggregatedVirtualTransactions = 0;
      if($this->connection->inTransaction()) {
        $this->connection->rollBack();
      }
    }

}
