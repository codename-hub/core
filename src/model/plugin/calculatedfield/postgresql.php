<?php
namespace codename\core\model\plugin\calculatedfield;

/**
 * Tell a postgreSQL model to add a calculated field to the select query
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-18
 */
class postgresql extends \codename\core\model\plugin\calculatedfield implements \codename\core\model\plugin\calculatedfield\calculatedfieldInterface {

  /**
   * @inheritDoc
   */
  public function get(): string
  {
    return $this->calculation . ' AS ' . $this->field->get();
  }
  
}
