<?php
namespace codename\core;

/**
 * extended PDO statement
 * that internally counts usage
 * for providing optimizations
 */
class extendedPdoStatement extends \PDOStatement
{
  /**
   * [protected description]
   * @var int
   */
  protected $executionCount = 0;

  /**
   * [protected description]
   * @var \PDO
   */
  protected $pdoInstance = null;

  /**
   * [__construct description]
   * @param \PDO $pdo [description]
   */
  protected function __construct(\PDO $pdo)
  {
    $this->pdoInstance = $pdo;
  }

  /**
   * @inheritDoc
   */
  public function execute($input_parameters = null)
  {
    $this->executionCount++;
    return parent::execute($input_parameters);
  }

  /**
   * [getExecutionCount description]
   * @return int [description]
   */
  public function getExecutionCount() : int {
    return $this->executionCount;
  }
}
