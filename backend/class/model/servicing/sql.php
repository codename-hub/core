<?php
namespace codename\core\model\servicing;

class sql extends \codename\core\model\servicing
{
  /**
   * [getTableIdentifier description]
   * @param  \codename\core\model\schematic\sql $model [description]
   * @return string                               [description]
   */
  public function getTableIdentifier(\codename\core\model\schematic\sql $model): string {
    return $model->schema . '.' . $model->table;
  }

  /**
   * [getSaveUpdateSetModifiedTimestampStatement description]
   * @param  \codename\core\model\schematic\sql $model [description]
   * @return string                               [description]
   */
  public function getSaveUpdateSetModifiedTimestampStatement(\codename\core\model\schematic\sql $model): string {
    return 'now()';
  }

  public function wrapIdentifier($identifier) {
    return $identifier;
  }

  public function getTableIdentifierParametrized($schema, $table) {
    return $schema . '.' . $table;
  }

  public function jsonEncode($data): string {
    return json_encode($data);
  }
}
