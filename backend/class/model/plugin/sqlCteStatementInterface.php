<?php
namespace codename\core\model\plugin;

/**
 * interface that symbolizes a CTE functionality
 * (Common Table Expression)
 */
interface sqlCteStatementInterface
{
  /**
   * [getSqlCteStatement description]
   * @param  string $cteName [description]
   * @param  array  &$params [PDO params tracking]
   * @param  string|null $refAlias
   * @return string          [description]
   */
  function getSqlCteStatement(string $cteName, array &$params, string $refAlias = null): string;
}
