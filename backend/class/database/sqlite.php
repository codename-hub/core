<?php

namespace codename\core\database;

use codename\core\app;
use codename\core\exception;
use codename\core\extendedPdoStatement;
use codename\core\observer\database;
use codename\core\sensitiveException;
use PDO;
use PDOException;

/**
 * SQLite db driver
 * @package core
 * @since 2020-01-03
 */
class sqlite extends \codename\core\database
{
    /**
     * [protected description]
     * @var array [type]
     */
    protected array $sqliteQueryLog = [];
    /**
     * [protected description]
     * @var bool
     */
    protected bool $emulationMode = false;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        $this->driver = 'sqlite';
        try {
            // set query log
            $this->queryLog = $config['querylog'] ?? null;

            if ($config['emulation_mode'] ?? false) {
                $this->emulationMode = true;
            }

            try {
                $file = $config['database_file'];
                if ($config['database_file_path_relative'] ?? false) {
                    $file = app::getHomedir($config['database_home']['vendor'], $config['database_home']['app']) . '/' . $file;
                }
                $this->connection = new PDO($this->driver . ":" . $file);
            } catch (\Exception $e) {
                throw new sensitiveException($e);
            }

            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, [extendedPdoStatement::class, [$this->connection]]);
        } catch (PDOException $e) {
            throw new exception(self::EXCEPTION_CONSTRUCT_CONNECTIONERROR, exception::$ERRORLEVEL_FATAL, $e);
        }

        $this->attach(new database());
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $query, array $params = []): void
    {
        if ($this->emulationMode) {
            $query = str_ireplace('NOW()', "strftime('%Y-%m-%d %H:%M:%S','now')", $query);
        }
        $this->sqliteQueryLog[] = $query;
        parent::query($query, $params);
    }

    /**
     * [getQueryLog description]
     * @return array [description]
     */
    public function getQueryLog(): array
    {
        return $this->sqliteQueryLog;
    }
}
