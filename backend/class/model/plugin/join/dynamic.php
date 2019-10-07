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
  public function dynamicJoin(array $result): array
  {
    // print_r("dynamic join!");
    $newResult = [];

    // print_r([
    //   'model' => $this->model->getIdentifier(),
    //   'modelField' => $this->modelField,
    //   'referenceField' => $this->referenceField,
    // ]);
    // print_r($result);

    foreach($result as $baseResultRow) {
      if($leftValue = $baseResultRow[$this->modelField]) {
        $res = $this->model->addFilter($this->referenceField, $leftValue)->search()->getResult();
        // echo("res!");
        // print_r($res);
        foreach($res as $partialResultRow) {
          $newResult[] = array_merge($baseResultRow, $partialResultRow);
        }
      } else {
        $newResult[] = $baseResultRow;
      }
    }

    // echo("new result:");
    // print_r($newResult);

    return $newResult;
  }


}
