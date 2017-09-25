<?php
namespace codename\core\model\schematic;
use \codename\core\app;

/**
 * MySQL's specific SQL commands
 * @package core
 * @author Kevin Dargel
 * @since 2017-03-01
 */
abstract class mysql extends \codename\core\model\schematic\sql implements \codename\core\model\modelInterface {

    /**
     * @todo DOCUMENTATION
     */
    CONST DB_TYPE = 'mysql';

    /**
     * @inheritDoc
     */
    protected function jsonEncode($data): string
    {
      // we need this option for mysql
      return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
