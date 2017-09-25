<?php
namespace codename\core\install;
/**
 * Description of what this does.
 *
 * @param
 * @return    void
 * @author
 * @copyright
 */
class dbStructureElement {

  const STATE_NONE = 'NONE';

  const STATE_OK = 'OK';
  const STATE_CHANGE = 'CHANGE';
  const STATE_MISSING_DATATYPE = 'MISSING_DATATYPE';
  const STATE_ADD = 'ADD';
  const STATE_MISSING_SCHEMA = 'MISSING_SCHEMA';

  const STATE_CHANGE_FOREIGNKEY = 'CHANGE_FOREIGNKEY';
  const STATE_CHANGE_PRIMARYKEY = 'CHANGE_PRIMARYKEY';
  const STATE_CHANGE_AUTOINCREMENT = 'CHANGE_AUTOINCREMENT';
  const STATE_CHANGE_UNIQUEKEY = 'CHANGE_UNIQUEKEY';

  const STATE_INVALID_CONFIG = 'INVALID_CONFIG';
  const STATE_INCOMPLETE_FOREIGN_KEY_CONFIG = 'INCOMPLETE_FOREIGN_KEY_CONFIG';

  public $driver = null;
  public $connection = null;
  public $schema = null;
  public $table = null;
  public $column = null;

  public $type = null;
  public $notnull = null;
  public $hasdefaultvalue = null;

  public $isprimarykey = false;
  public $isunique = false;

  public $dbdoc_state = null;
  public $dbdoc_info = null;
  public $dbdoc_data = null;
}
