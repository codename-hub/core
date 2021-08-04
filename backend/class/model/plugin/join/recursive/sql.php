<?php
namespace codename\core\model\plugin\join\recursive;

abstract class sql extends \codename\core\model\plugin\join\recursive
  implements \codename\core\model\plugin\sqlCteStatementInterface
{
  /**
   * [protected description]
   * @var \codename\core\model\schematic\sql
   */
  public $model = null;

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(
    \codename\core\model $model,
    string $selfReferenceField,
    string $anchorField,
    array $anchorConditions,
    string $type,
    $modelField,
    $referenceField,
    array $conditions = [],
    ?string $virtualField = null
  ) {
    // We exchange referenceField with the fixed anchor field name
    $this->internalReferenceField = $referenceField;
    $referenceField = $this->anchorFieldName; // default name
    parent::__CONSTRUCT($model, $selfReferenceField, $anchorField, $anchorConditions, $type, $modelField, $referenceField, $conditions, $virtualField);
  }

  /**
   * [protected description]
   * @var [type]
   */
  protected $internalReferenceField = null;

  /**
   * [anchorFieldName description]
   * @var string
   */
  protected $anchorFieldName = '__anchor';

  /**
   * @inheritDoc
   */
  public function getSqlCteStatement(string $cteName, array &$params, string $refAlias = null): string
  {
    // return "WITH RECURSIVE <name>
    //   AS (
    //     SELECT <pkey> as ___anchor, <tableIdentifier>.*
    //     FROM <tableIdentifier>
    //     WHERE <anchorConditions>
    //     UNION ALL
    //     SELECT <name>.___anchor, <tableIdentifier>.*
    //     FROM <tableIdentifier>, <name>
    //     WHERE <name>.<selfReferenceField> = <tableIdentifier>.<pkey>
    //   )";

    $anchorConditionQuery = '';
    if(count($this->anchorConditions) > 0) {
      $anchorConditionQuery = 'WHERE '.\codename\core\model\schematic\sql::convertFilterQueryArray(
        $this->model->getFilters($this->anchorConditions, [], [], $params, $refAlias) // ??
      );
    }

    // if table is already a CTE, inherit it.
    $refName = $refAlias ?? $this->model->getTableIdentifier();

    //
    // CTE Prefix / "WITH [RECURSIVE]" is implicitly added by the model class
    //
    return "{$cteName} "
      . " AS ( "
      . "  SELECT "
      //        We default to the PKEY as (visible) anchor field:
      //        Default anchor field name (__anchor)
      //        Not to be confused with recursiveAnchorField
      . "      {$this->model->getPrimarykey()} as {$this->anchorFieldName}"
      // . "    , 0 as __level " // TODO: internal level tracking for keeping order?

      // Endless loop / circular reference protection for array-supporting RDBMS:
      // . "    , array[{$this->model->getPrimarykey()}] as __traversed "

      . "    , {$refName}.* "
      . "  FROM {$refName} "
      . "  {$anchorConditionQuery} "

      //   NOTE: UNION instead of UNION ALL prevents duplicates
      //   and is an implicit termination condition for the recursion
      //   as the some query might return rows already selected
      //   leading to 'zero added rows' - and finishing our query
      . "  UNION "

      . "  SELECT "
      . "      {$cteName}.{$this->anchorFieldName} "
      // . "    , __level+1 " // TODO: internal level tracking for keeping order?

      // Endless loop / circular reference protection for array-supporting RDBMS:
      // . "    , {$cteName}.__traversed || {$refName}.{$this->getPrimarykey()} "

      . "    , {$refName}.* "
      . "  FROM {$refName}, {$cteName} "
      . "  WHERE {$cteName}.{$this->selfReferenceField} = {$refName}.{$this->anchorField} "
      // . "  ORDER BY {$cteName}.{$this->anchorFieldName}, __level" // TODO: internal level tracking for keeping order?

      // Endless loop / circular reference protection for array-supporting RDBMS:
      // . "  AND {$this->model->getPrimarykey()} <> ALL ({$cteName}.__traversed) "
      . ")";
  }
}
