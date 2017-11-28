<?php
namespace codename\core\model\plugin\join;
use codename\core\exception;
use codename\core\model\plugin\join;

/**
 * Provide model joining as a plugin
 * MySQL Implementation
 * @package core
 * @since 2017-11-28
 */
class mysql extends \codename\core\model\plugin\join {

  /**
   * @inheritDoc
   */
  public function getJoinMethod(): string
  {
    switch($this->type) {
      case self::TYPE_LEFT:
        return 'LEFT JOIN';
      case self::TYPE_RIGHT:
        return 'RIGHT JOIN';
      case self::TYPE_FULL:
        return 'FULL JOIN';
      case self::TYPE_INNER:
        return 'INNER JOIN';
      case self::TYPE_DEFAULT:
        return null;
    }
    throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
  }

}