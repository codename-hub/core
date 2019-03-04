<?php
namespace codename\core\model\plugin;

/**
 * Plugin for creating fulltext fields
 * @package core
 * @author Ralf Thieme
 * @since 2019-03-04
 */
abstract class fulltext extends \codename\core\model\plugin implements \codename\core\model\plugin\fulltext\fulltextInterface {

  /**
   * [public description]
   * @var \codename\core\value\text\modelfield
   */
  public $field = null;

  /**
   * [public description]
   * @var array
   */
  public $fields = [];

  /**
   * [public description]
   * @var string
   */
  public $value = '';

  /**
   * [__CONSTRUCT description]
   * @param  \codename\core\value\text\modelfield $field  [description]
   * @param  string                          $value  [description]
   * @param  array                           $fields [description]
   * @return [type]                                  [description]
   */
  public function __CONSTRUCT(\codename\core\value\text\modelfield $field, string $value, array $fields) {
    foreach($fields as $thisfield) {
      if (!$thisfield instanceof \codename\core\value\text\modelfield) {
        throw new \codename\core\exception('EXCEPTION_MODEL_PLUGIN_FULLTEXT_BAD_FIELD', \codename\core\exception::$ERRORLEVEL_FATAL, $thisfield);
      }
    }
    $this->field = $field;
    $this->fields = $fields;
    $this->value = $value;
    return $this;
  }

}
