<?php
namespace codename\core\model\plugin;

interface managedFilterInterface
{
  /**
   * returns parameters needed to be provided as variable names
   * in the variableMap in ::getFilterQuery()
   * @return array [description]
   */
  function getFilterQueryParameters() : array;

  /**
   * returns the complete filter query
   *
   * @param  array  $variableNameMap [description]
   * @param  [type] $tableAlias  [description]
   * @return string              [description]
   */
  function getFilterQuery(array $variableNameMap, $tableAlias = null) : string;
}
