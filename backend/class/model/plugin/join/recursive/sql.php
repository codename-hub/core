<?php

namespace codename\core\model\plugin\join\recursive;

use codename\core\exception;
use codename\core\model;
use codename\core\model\plugin\join\recursive;
use codename\core\model\plugin\sqlCteStatementInterface;

abstract class sql extends recursive implements sqlCteStatementInterface
{
    /**
     * [protected description]
     * @var model\schematic\sql
     */
    public $model = null;
    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $internalReferenceField = null;
    /**
     * [anchorFieldName description]
     * @var string
     */
    protected string $anchorFieldName = '__anchor';

    /**
     * {@inheritDoc}
     */
    public function __construct(
        model $model,
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
        parent::__construct($model, $selfReferenceField, $anchorField, $anchorConditions, $type, $modelField, $referenceField, $conditions, $virtualField);
    }

    /**
     * {@inheritDoc}
     * @param string $cteName
     * @param array $params
     * @param string|null $refAlias
     * @return string
     * @throws exception
     */
    public function getSqlCteStatement(string $cteName, array &$params, string $refAlias = null): string
    {
        $anchorConditionQuery = '';
        if (count($this->anchorConditions) > 0) {
            $anchorConditionQuery = 'WHERE ' . model\schematic\sql::convertFilterQueryArray(
                $this->model->getFilters($this->anchorConditions, [], [], $params, $refAlias) // ??
            );
        }

        // if table is already a CTE, inherit it.
        $refName = $refAlias ?? $this->model->getTableIdentifier();

        //
        // CTE Prefix / "WITH [RECURSIVE]" is implicitly added by the model class
        //
        return "$cteName "
          . " AS ( "
          . "  SELECT "
          //        We default to the PKEY as (visible) anchor field:
          //        Default anchor field name (__anchor)
          //        Not to be confused with recursiveAnchorField
          . "      {$this->model->getPrimaryKey()} as $this->anchorFieldName"
          // . "    , 0 as __level " // TODO: internal level tracking for keeping order?

          // Endless loop / circular reference protection for array-supporting RDBMS:
          // . "    , array[{$this->model->getPrimaryKey()}] as __traversed "

          . "    , $refName.* "
          . "  FROM $refName "
          . "  $anchorConditionQuery "

          //   NOTE: UNION instead of UNION ALL prevents duplicates
          //   and is an implicit termination condition for the recursion
          //   as some query might return rows already selected
          //   leading to 'zero added rows' - and finishing our query
          . "  UNION "

          . "  SELECT "
          . "      $cteName.$this->anchorFieldName "
          // . "    , __level+1 " // TODO: internal level tracking for keeping order?

          // Endless loop / circular reference protection for array-supporting RDBMS:
          // . "    , {$cteName}.__traversed || {$refName}.{$this->getPrimaryKey()} "

          . "    , $refName.* "
          . "  FROM $refName, $cteName "
          . "  WHERE $cteName.$this->selfReferenceField = $refName.$this->anchorField "
          // . "  ORDER BY {$cteName}.{$this->anchorFieldName}, __level" // TODO: internal level tracking for keeping order?

          // Endless loop / circular reference protection for array-supporting RDBMS:
          // . "  AND {$this->model->getPrimaryKey()} <> ALL ({$cteName}.__traversed) "
          . ")";
    }
}
