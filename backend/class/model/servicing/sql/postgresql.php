<?php
namespace codename\core\model\servicing\sql;

class postgresql extends \codename\core\model\servicing\sql
{
  /**
   * @inheritDoc
   */
  public function wrapIdentifier($identifier)
  {
    return '`' . $identifier . '`';
  }
}
