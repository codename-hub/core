<?php
namespace codename\core\model\plugin\calculation;

use codename\core\exception;

/**
 * Tell a MySQL model to add a calculated field to the select query
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
class mysql extends \codename\core\model\plugin\calculation implements \codename\core\model\plugin\calculation\calculationInterface {

  /**
   * @inheritDoc
   */
  public function get() : string
  {
    $sql = '';
    switch ($this->calculationType) {
      case 'count':
        $sql = 'COUNT('.$this->fieldBase->get().')';
        break;
      case 'sum':
        $sql = 'SUM('.$this->fieldBase->get().')';
        break;
      case 'avg':
        $sql = 'AVG('.$this->fieldBase->get().')';
        break;
      default:
        throw new exception('EXCEPTION_MODEL_PLUGIN_CALCULATION_MYSQL_UNKKNOWN_CALCULATION_TYPE', exception::$ERRORLEVEL_ERROR, $this->calculationType);
        break;
    }
    return $sql . ' AS ' . $this->field->get();
  }

}
