<?php

namespace codename\core;

use PDO;
use PDOStatement;

/**
 * extended PDO statement
 * that internally counts usage
 * for providing optimizations
 */
class extendedPdoStatement extends PDOStatement
{
    /**
     * [protected description]
     * @var int
     */
    protected int $executionCount = 0;

    /**
     * [protected description]
     * @var null|PDO
     */
    protected ?PDO $pdoInstance = null;

    /**
     * [__construct description]
     * @param PDO $pdo [description]
     */
    protected function __construct(PDO $pdo)
    {
        $this->pdoInstance = $pdo;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($params = null): bool
    {
        $this->executionCount++;
        return parent::execute($params);
    }

    /**
     * [getExecutionCount description]
     * @return int [description]
     */
    public function getExecutionCount(): int
    {
        return $this->executionCount;
    }
}
