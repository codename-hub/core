<?php
namespace codename\core\model\servicing\sql;

class sqlite extends \codename\core\model\servicing\sql
{
  /**
   * @inheritDoc
   */
  public function getTableIdentifier(\codename\core\model\schematic\sql $model): string {
    //
    // SQLite doesn't support schema.table syntax, as there's only one database
    // therefore, we 'fake' it by using `schema.table`
    //
    return '`'.$model->schema . '.' . $model->table.'`';
  }

  /**
   * @inheritDoc
   */
  public function getSaveUpdateSetModifiedTimestampStatement(\codename\core\model\schematic\sql $model): string {
    //
    // SQLite implementation differs from other SQL databases
    //
    return 'datetime(\'now\')';
  }

  /**
   * @inheritDoc
   */
  public function getTableIdentifierParametrized($schema, $table)
  {
    return '`'.$schema . '.' . $table.'`';
  }

  /**
   * @inheritDoc
   */
  public function jsonEncode($data): string
  {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}
