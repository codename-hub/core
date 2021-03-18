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

    /**
     * @inheritDoc
     */
    protected function jsonEncode($data): string
    {
      // we need this option for mysql
      return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @inheritDoc
     */
    protected function getTableIdentifier(): string
    {
      //
      // SQLite doesn't support schema.table syntax, as there's only one database
      // therefore, we 'fake' it by using `schema.table`
      //
      return '`'.$this->schema . '.' . $this->table.'`';
    }

    /**
     * Statement/SQL to use for setting current datetime to _modified fields
     * Specialty for SQLite: we have to rely on datetime('now')
     * @var string
     */
    protected $saveUpdateSetModifiedTimestampStatement = 'datetime(\'now\')';

    /**
     * @inheritDoc
     */
    protected function getCurrentFieldlistNonRecursive(
      string $alias = null,
      array &$params
    ): array {
      $value = parent::getCurrentFieldlistNonRecursive($alias, $params);

      $fields = [];
      foreach($value as $f) {
        if(count($f) === 3) {
          $fields[] = ["`{$f[0]}.$f[1]`", $f[2]];
        } else {
          $fields[] = $f;
        }
      }

      return $fields;
    }
}
