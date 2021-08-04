<?php
namespace codename\core\model\plugin\join;
use codename\core\exception;
use codename\core\model\plugin\join;

/**
 * Provide model joining as a plugin
 * SQLite Implementation
 * @package core
 * @since 2021-03-18
 */
class sqlite extends \codename\core\model\plugin\join {

  /**
   * @inheritDoc
   */
  public function getJoinMethod(): string
  {
    switch($this->type) {
      case self::TYPE_LEFT:
        return 'LEFT JOIN';
      case self::TYPE_RIGHT:
      case self::TYPE_FULL:
        // not supported on SQLite
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_SQLITE_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
      case self::TYPE_INNER:
        return 'INNER JOIN';
      case self::TYPE_DEFAULT:
        return null;
    }
    throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
  }

}
