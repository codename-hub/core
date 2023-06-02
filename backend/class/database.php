<?php

namespace codename\core;

use PDO;
use PDOException;
use PDOStatement;
use ReflectionException;

/**
 * The main database class that uses the php pdo-object for SQL database interaction
 * @package core
 * @since 2016-01-06
 */
class database extends observable
{
    /**
     * It seems there's a problem when connecting to the desired database server.
     * The server may be offline, misconfigured or your configuration is wrong.
     * @var string
     */
    public const EXCEPTION_CONSTRUCT_CONNECTIONERROR = 'EXCEPTION_CONSTRUCT_CONNECTIONERROR';

    /**
     * The query that was being executed id not finis correctly.
     * It may contain errors
     * @var string
     */
    public const EXCEPTION_QUERY_QUERYERROR = 'EXCEPTION_QUERY_QUERYERROR';

    /**
     * Contains the current driver name
     * @var null|string
     */
    public ?string $driver = null;

    /**
     * Contains the \PDO instance of this DB instance
     * @var null|PDO
     */
    protected ?PDO $connection = null;

    /**
     * Contains the \PDOStatement instance of this DB instance after performing a query
     * @var null|PDOStatement
     */
    protected ?PDOStatement $statement = null;

    /**
     * log name for queries
     * null to disable
     * @var string|null
     */
    protected ?string $queryLog = null;
    /**
     * [protected description]
     * @var PDOStatement[]
     */
    protected array $statements = [];
    /**
     * holds data about the amount/count of cached statements
     * to avoid calls to count() as far as possible
     * @var int
     */
    protected int $statementsCount = 0;
    /**
     * limit of how many PDO Prepared Statement Instances are kept for this database
     * @var int
     */
    protected int $maximumCachedStatements = 100;
    /**
     * [protected description]
     * @var int
     */
    protected int $maximumCachedOptimizedStatements = 50;
    /**
     * Virtual Transaction Counter
     * @var array
     */
    protected array $virtualTransactions = [];
    /**
     * Global virtual transaction counter
     * @var int
     */
    protected int $aggregatedVirtualTransactions = 0;

    /**
     * Creates an instance with the given data
     * @param array $config
     * @throws exception
     * @throws sensitiveException
     */
    public function __construct(array $config)
    {
        try {
            if (isset($config['env_pass'])) {
                $pass = getenv($config['env_pass']);
            } elseif (isset($config['pass'])) {
                $pass = $config['pass'];
            } else {
                throw new exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, exception::$ERRORLEVEL_FATAL, ['ENV_PASS_NOT_SET']);
            }

            if (isset($config['env_host'])) {
                $host = getenv($config['env_host']);
            } elseif (isset($config['host'])) {
                $host = $config['host'];
            } else {
                throw new exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, exception::$ERRORLEVEL_FATAL, ['ENV_HOST_NOT_SET']);
            }

            if (isset($config['env_user'])) {
                $user = getenv($config['env_user']);
            } elseif (isset($config['user'])) {
                $user = $config['user'];
            } else {
                throw new exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, exception::$ERRORLEVEL_FATAL, ['ENV_USER_NOT_SET']);
            }

            // set query log
            $this->queryLog = $config['querylog'] ?? null;

            // allow connections without database name
            // just put in autoconnect_database = false
            $autoconnectDatabase = true;
            if (isset($config['autoconnect_database'])) {
                $autoconnectDatabase = $config['autoconnect_database'];
            }

            try {
                // CHANGED 2021-05-04: allow driver-specific default attrs, if any.
                $attrs = $this->getDefaultAttributes();
                // CHANGED 2023-05-25: fixed dns driver for postgresql
                $driver = $this->driver;
                if ($driver === 'postgresql') {
                    $driver = 'pgsql';
                }
                $this->connection = new PDO($driver . ":" . ($autoconnectDatabase ? "dbname=" . $config['database'] . ";" : '') . 'host=' . $host . (isset($config['port']) ? (';port=' . $config['port']) : '') . (isset($config['charset']) ? (';charset=' . $config['charset']) : ''), $user, $pass, $attrs);
            } catch (\Exception $e) {
                throw new sensitiveException($e);
            }

            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // if using NON-PERSISTENT connections, we override the statement class with one of our own
            // WARNING/CHANGED 2021-09-23: added ctor arg as reference (&)
            // as it prevents PHP's GC from collecting this properly
            // and this may cause connection keepalive trouble, when re-connecting to the same server
            // in the same process.
            $this->connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, [extendedPdoStatement::class, [&$this->connection]]);
        } catch (PDOException $e) {
            throw new exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, exception::$ERRORLEVEL_FATAL, $e);
        }

        $this->attach(new observer\database());
        return $this;
    }

    /**
     * Default attributes to use during connection creation
     * as some attributes have no effect otherwise
     * @return array
     */
    protected function getDefaultAttributes(): array
    {
        return [];
    }

    /**
     * [__destruct description]
     */
    public function __destruct()
    {
        //
        // WARNING/CHANGED 2021-09-23: added destructor removing PDO object ref
        // see note in this classes' constructor.
        //
        $this->connection = null;
    }

    /**
     * Performs the given $query on the \PDO instance.
     * Stores the \PDOStatement to the instance for result management
     * @param string $query
     * @param array $params
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function query(string $query, array $params = []): void
    {
        if ($this->queryLog) {
            app::getLog($this->queryLog)->debug($query);
        }

        $this->statement = null;
        foreach ($this->statements as $statement) {
            if ($statement->queryString == $query) {
                $this->statement = $statement;
                break;
            }
        }
        if ($this->statement === null) {
            $this->statement = $this->connection->prepare($query);

            //
            // Clear cached prepared PDO statements, if there are more than N of them
            //
            if ($this->statementsCount > $this->maximumCachedStatements) {
                if ($this->maximumCachedOptimizedStatements) {
                    uasort($this->statements, function (extendedPdoStatement $a, extendedPdoStatement $b) {
                        return $a->getExecutionCount() <=> $b->getExecutionCount();
                    });
                    $this->statements = array_slice($this->statements, 0, $this->maximumCachedOptimizedStatements);
                    $this->statementsCount = count($this->statements);
                } else {
                    $this->statements = [];
                    $this->statementsCount = 0;
                }
            }

            $this->statements[] = $this->statement;
            $this->statementsCount++;
        }

        foreach ($params as $key => $param) {
            // use parameters set in getParametrizedValue
            // 0 => value, 1 => \PDO::PARAM_...
            $this->statement->bindValue($key, $param[0], $param[1]);
        }

        $res = $this->statement->execute();

        // explicitly check for falseness identity, not only == (equality), which may evaluate a 0 to a false.
        if ($res === false) {
            throw new exception(self::EXCEPTION_QUERY_QUERYERROR, exception::$ERRORLEVEL_FATAL, ['errors' => $this->statement->errorInfo(), 'query' => $query, 'params' => $params]);
        }
    }

    /**
     * Returns the array of records in the result
     * @return array
     */
    public function getResult(): array
    {
        if (is_null($this->statement)) {
            return [];
        }
        return $this->statement->fetchAll(PDO::FETCH_NAMED);
    }

    /**
     * Returns the last inserted ID for the past query
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Returns count of affected rows of last
     * create, update or delete operation
     * @return int|null
     */
    public function affectedRows(): ?int
    {
        return $this->statement?->rowCount();
    }

    /**
     * [getConnection description]
     * @return PDO [description]
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Starts a virtualized transaction
     * that may handle multimodal transactions
     *
     * @param string $transactionName [description]
     * @return void
     * @throws exception
     */
    public function beginVirtualTransaction(string $transactionName = 'default'): void
    {
        if (!isset($this->virtualTransactions[$transactionName])) {
            $this->virtualTransactions[$transactionName] = 0;
        }
        if (($this->virtualTransactions[$transactionName] === 0) && ($this->aggregatedVirtualTransactions === 0)) {
            // this may cause errors when using multiple transaction names...
            if ($this->connection->inTransaction()) {
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
     * @param string $transactionName [description]
     * @return void [type]                  [description]
     * @throws exception
     */
    public function endVirtualTransaction(string $transactionName = 'default'): void
    {
        if (!isset($this->virtualTransactions[$transactionName]) || ($this->virtualTransactions[$transactionName] === 0)) {
            throw new exception('EXCEPTION_DATABASE_VIRTUALTRANSACTION_END_DOESNOTEXIST', exception::$ERRORLEVEL_FATAL, $transactionName);
        }
        if (!$this->connection->inTransaction()) {
            throw new exception('EXCEPTION_DATABASE_VIRTUALTRANSACTION_END_TRANSACTION_INTERRUPTED', exception::$ERRORLEVEL_FATAL, $transactionName);
        }

        $this->virtualTransactions[$transactionName]--;
        $this->aggregatedVirtualTransactions--;

        if (($this->virtualTransactions[$transactionName] === 0) && ($this->aggregatedVirtualTransactions === 0)) {
            $this->connection->commit();
        }
    }

    /**
     * performs a full rollback of currently pending transactions
     * NOTE: this kills _all_ applicable transactions.
     * @return void
     */
    public function rollback(): void
    {
        $this->virtualTransactions = [];
        $this->aggregatedVirtualTransactions = 0;
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}
