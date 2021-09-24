<?php
namespace codename\core\model\plugin\join;
use codename\core\exception;
use codename\core\model\plugin\join;

/**
 * Bare Joining Plugin
 * @package core
 * @since 2017-12-05
 */
class bare extends \codename\core\model\plugin\join implements \codename\core\model\plugin\join\executableJoinInterface {

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
   * @param  array $left  [left side rows]
   * @param  array $right [right side rows]
   * @return array        [result]
   */
  protected function internalJoin(array $left, array $right, string $leftField, string $rightField) : array {
    $success = array_walk ($left, function(array &$leftValue, $key, array $userDict) {
      $right = $userDict[0];
      $leftField = $userDict[1];
      $rightField = $userDict[2];

      $found = false;
      if(isset($leftValue[$leftField])) {
        foreach($right as $rightValue) {
          if(($leftValue[$leftField] ?? null) == ($rightValue[$rightField] ?? null)) {
            $leftValue = array_merge($leftValue, $rightValue);
            $found = true;
            break;
          }
        }
      }
      if(!$found) {
        if($this->getJoinMethod() == self::TYPE_INNER) {
          $leftValue = null;
        } else if($this->getJoinMethod() == self::TYPE_LEFT) {
          $emptyFields = [];
          foreach($this->model->config->get('field') as $field) {
            $emptyFields[$field] = null;
          }
          $leftValue = array_merge($leftValue, $emptyFields);
        }
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
