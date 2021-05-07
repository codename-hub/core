<?php
namespace codename\core\model\plugin\join;

use codename\core\exception;

abstract class recursive extends \codename\core\model\plugin\join
{
  /**
   * Field that is used for self-reference
   * selfReferenceField => anchorField
   * @var string
   */
  protected $selfReferenceField = null;

  /**
   * Field that is used as anchor point
   * @var string
   */
  protected $anchorField = null;

  /**
   * [protected description]
   * @var \codename\core\model\plugin\filter
   */
  protected $anchorConditions = [];

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(
    \codename\core\model $model,
    string $selfReferenceField,
    string $anchorField,
    array $anchorConditions,
    string $type,
    $modelField,
    $referenceField,
    array $conditions = [],
    ?string $virtualField = null
  ) {
    parent::__CONSTRUCT($model, $type, $modelField, $referenceField, $conditions, $virtualField);
    $this->selfReferenceField = $selfReferenceField;
    $this->anchorField = $anchorField;
    if(count($anchorConditions) > 0) {
      foreach($anchorConditions as $cond) {
        if($cond instanceof \codename\core\model\plugin\filter) {
          $this->anchorConditions[] = $cond;
        } else {
          $this->anchorConditions[] = $this->createFilterPluginInstance($cond);
        }
      }
    } else {
      // throw new exception('PLUGIN_JOIN_RECURSIVE_ANCHOR_CONDITIONS_REQUIRED', exception::$ERRORLEVEL_ERROR);
    }
  }

  /**
   * [createFilterPluginInstance description]
   * @param  array                               $data [description]
   * @return \codename\core\model\plugin\filter        [description]
   */
  protected abstract function createFilterPluginInstance(array $data): \codename\core\model\plugin\filter;
}
