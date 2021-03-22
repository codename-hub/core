<?php
namespace codename\core\model\plugin\aggregate;

use codename\core\exception;

/**
 * Tell a SQLite model to add a calculated field to the select query
 * @package core
 * @author Kevin Dargel
 * @since 2021-03-22
 */
class sqlite extends \codename\core\model\plugin\aggregate implements \codename\core\model\plugin\aggregate\aggregateInterface {

  /**
   * @inheritDoc
   */
  public function get(string $tableAlias = null) : string
  {
    $sql = '';
    $tableAlias = $tableAlias ? $tableAlias.'.' : '';
    switch ($this->calculationType) {
      case 'count':
        $sql = 'COUNT('.$tableAlias.$this->fieldBase->get().')';
        break;
      case 'count_distinct':
        $sql = 'COUNT(DISTINCT '.$tableAlias.$this->fieldBase->get().')';
        break;
      case 'sum':
        $sql = 'SUM('.$tableAlias.$this->fieldBase->get().')';
        break;
      case 'avg':
        $sql = 'AVG('.$tableAlias.$this->fieldBase->get().')';
        break;
      case 'year':
        $sql = 'strftime(\'%Y\','.$tableAlias.$this->fieldBase->get().')';
        break;
      // case 'quarter':
      //   $sql = 'QUARTER('.$tableAlias.$this->fieldBase->get().')';
      //   break;
      case 'month':
        $sql = 'strftime(\'%m\','.$tableAlias.$this->fieldBase->get().')';
        break;
      case 'day':
        $sql = 'strftime(\'%d\','.$tableAlias.$this->fieldBase->get().')';
        break;
      // case 'timestampdiff-year':
      //   $sql = 'TIMESTAMPDIFF(YEAR, '.$tableAlias.$this->fieldBase->get().', CURDATE())';
      //   break;
      default:
        throw new exception('EXCEPTION_MODEL_PLUGIN_CALCULATION_SQLITE_UNKKNOWN_CALCULATION_TYPE', exception::$ERRORLEVEL_ERROR, $this->calculationType);
        break;
    }
    return $sql . ' AS ' . $this->field->get();
  }

}
