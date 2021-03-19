<?php
namespace codename\core\model\schematic;
use \codename\core\app;

/**
 * SQLite's specific SQL commands
 * @package core
 * @author Kevin Dargel
 * @since 2020-01-03
 */
abstract class sqlite extends \codename\core\model\schematic\sql implements \codename\core\model\modelInterface {

    /**
     * @todo DOCUMENTATION
     */
    CONST DB_TYPE = 'sqlite';

    // /**
    //  * @inheritDoc
    //  */
    // protected function jsonEncode($data): string
    // {
    //   // we need this option for mysql
    //   return json_encode($data, JSON_UNESCAPED_UNICODE);
    // }

    // /**
    //  * @inheritDoc
    //  */
    // protected function getTableIdentifier(): string
    // {
    //   return $this->getServicingSqlInstance()->getTableIdentifier($this);
    // }

    // /**
    //  * @inheritDoc
    //  */
    // protected function getCurrentFieldlistNonRecursive(
    //   string $alias = null,
    //   array &$params
    // ): array {
    //   $value = parent::getCurrentFieldlistNonRecursive($alias, $params);
    //
    //   $fields = [];
    //   foreach($value as $f) {
    //     if(count($f) === 3) {
    //       $fields[] = ["`{$f[0]}.$f[1]`", $f[2]];
    //     } else {
    //       $fields[] = $f;
    //     }
    //   }
    //
    //   return $fields;
    // }
}
