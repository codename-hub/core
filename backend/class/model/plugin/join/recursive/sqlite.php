<?php
namespace codename\core\model\plugin\join\recursive;

use codename\core\exception;

class sqlite extends \codename\core\model\plugin\join\recursive\sql
{
  /**
   * @inheritDoc
   */
  public function getJoinMethod(): string
  {
    switch($this->type) {
      case self::TYPE_LEFT:
        return 'LEFT JOIN';
      case self::TYPE_RIGHT:
        // return 'RIGHT JOIN';
      case self::TYPE_FULL:
        // not supported on MySQL
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_RECURSIVE_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
      case self::TYPE_INNER:
        return 'INNER JOIN';
      case self::TYPE_DEFAULT:
        return null;
    }
    throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_RECURSIVE_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
  }

  /**
   * @inheritDoc
   */
  protected function createFilterPluginInstance(
    array $data
  ): \codename\core\model\plugin\filter {
    return new \codename\core\model\plugin\filter\sqlite(
      \codename\core\value\text\modelfield::getInstance($data['field']),
      $data['value'],
      $data['operator'],
      $data['conjunction'] ?? null, // ??
    );
  }
}
