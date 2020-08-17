<?php
namespace codename\core\model\plugin\join;

use codename\core\exception;

class dynamic extends \codename\core\model\plugin\join implements dynamicJoinInterface {

  /**
   * @inheritDoc
   */
  public function getJoinMethod(): string
  {
    switch($this->type) {
      case self::TYPE_LEFT:
        return $this->type;
      /*case self::TYPE_RIGHT:
        return $this->type;*/
      /*case self::TYPE_FULL:
        // not supported on MySQL
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_MYSQL_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
      case self::TYPE_INNER:
        return 'INNER JOIN';*/
      case self::TYPE_DEFAULT:
        return self::TYPE_LEFT; // default fallback
      case self::TYPE_INNER:
        return $this->type;
    }
    throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
  }

  /**
   * @inheritDoc
   */
  public function dynamicJoin(array $result, ?array $params = null): array
  {
    $newResult = [];

    //
    // CHANGED 2020-07-22 vkey handling inside dynamic joins
    //
    $vKey = $params['vkey'];

    foreach($result as $baseResultRow) {
      //
      // If we have a FKEY value provided, query for the dataset
      // using the given model (and all of its descendants!)
      //
      if($leftValue = $baseResultRow[$this->modelField]) {

        //
        // TODO: we might backup the filters/filtercollections first
        // and re-apply them afterwards
        // NOTE: this might get risky, if you only apply regular filters before
        // and not defaultfilters. It should not break the logic!
        //
        $res = $this->model->addFilter($this->referenceField, $leftValue)->search()->getResult();

        foreach($res as $partialResultRow) {
          //
          // CHANGED 2020-07-22 vkey handling inside dynamic joins
          //
          if($vKey) {
            $newResult[] = array_merge(
              $baseResultRow,
              [
                $vKey => $partialResultRow
              ]
            );
          } else {
            $newResult[] = array_merge($baseResultRow, $partialResultRow);
          }
        }
      } else {
        // In case of empty FKEY value
        // simply output the base result without added data
        $newResult[] = $baseResultRow;
      }
    }

    return $newResult;
  }


}
