<?php
namespace codename\core\model\plugin\join;
use codename\core\exception;
use codename\core\model\plugin\join;

/**
 * Json Joining Plugin
 * @package core
 * @since 2018-02-21
 */
class json extends \codename\core\model\plugin\join implements \codename\core\model\plugin\join\executableJoinInterface {

  /**
   * defines that we're joining via an array key, not via it's contents
   * @var bool
   */
  protected $joinByArrayKey = true;


  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(
    \codename\core\model $model,
    string $type,
    $modelField,
    $referenceField,
    array $conditions = [],
    ?string $virtualField = null
  ) {
    parent::__CONSTRUCT($model, $type, $modelField, $referenceField, $conditions, $virtualField);
    $this->joinByArrayKey = $this->model->getPrimarykey() == $referenceField;
  }


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
   * [internalJoin description]
   * @param  array  $left       [left side dataset]
   * @param  array  $right      [right side dataset]
   * @param  string $leftField  [left field to join upon]
   * @param  string $rightField [right field to join upon]
   * @return array              [merged/reduced/expanded structures/datasets]
   */
  protected function internalJoin(array $left, array $right, string $leftField, string $rightField) : array {
    $success = array_walk ($left, function(array &$leftValue, $key, array $userDict) {
      $right = $userDict[0];
      $leftField = $userDict[1];
      $rightField = $userDict[2];

      $found = false;

      if($this->joinByArrayKey) {
        if(isset($right[$leftValue[$leftField]])) {
          $leftValue = array_merge($leftValue, $right[$leftValue[$leftField]]);
          $found = true;
        }

      } else {
        foreach($right as $rightValue) {
          if($leftValue[$leftField] == $rightValue[$rightField]) {
            $leftValue = array_merge($leftValue, $rightValue);
            $found = true;
            break;

          }
        }
      }
      if(!$found && $this->getJoinMethod() == self::TYPE_INNER) {
        $leftValue = null;
      }
    }, [$right, $leftField, $rightField]);

    if(!$success) {
      // error?
    }

    // kick out empty array elements previously set NULL
    if($this->getJoinMethod() == self::TYPE_INNER) {
      $left = array_filter($left, function($v, $k) {
        return $v != null;
      }, ARRAY_FILTER_USE_BOTH);
    }

    return $left;
  }

  /**
   * @inheritDoc
   */
  public function join(array $left, array $right) : array {
    if($this->getJoinMethod() == self::TYPE_LEFT) {
      return $this->internalJoin($left, $right, $this->modelField, $this->referenceField);
    } else if($this->getJoinMethod() == self::TYPE_RIGHT) {
      return $this->internalJoin($right, $left, $this->referenceField, $this->modelField);
    } else if($this->getJoinMethod() == self::TYPE_INNER) {
      return $this->internalJoin($left, $right, $this->modelField, $this->referenceField);
    }
    return $left;
  }

}
