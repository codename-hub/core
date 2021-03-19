<?php
namespace codename\core\model\servicing\sql;

class mysql extends \codename\core\model\servicing\sql
{
  /**
   * @inheritDoc
   */
  public function jsonEncode($data): string
  {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}
