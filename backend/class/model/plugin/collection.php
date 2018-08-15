<?php
namespace codename\core\model\plugin;

use codename\core\exception;

/**
 * Provide many to many relationship functionality as a plugin
 * @package core
 * @since 2018-01-08
 */
class collection extends \codename\core\model\plugin {

  /**
   * the original model (base)
   * this model's result gets extended by the collection data
   * @var \codename\core\model
   */
  public $baseModel = null;

  /**
   * the collection model
   * @var \codename\core\model
   */
  public $collectionModel = null;

  /**
   * Field in the original model the data will reside in
   * @var \codename\core\value\text\modelfield
   */
  public $field = null;

  /**
   * Undocumented function
   *
   * @param \codename\core\value\text\modelfield $field
   * @param \codename\core\model $baseModel
   * @param \codename\core\model $collectionModel
   */
  public function __construct(\codename\core\value\text\modelfield $field, \codename\core\model $baseModel, \codename\core\model $collectionModel) {
    $this->field = $field;
    $this->baseModel = $baseModel;
    $this->collectionModel = $collectionModel;

    // prepare some data
    foreach($this->collectionModel->config->get('foreign') as $fkey => $fcfg) {
      if($fcfg['model'] == $this->baseModel->getIdentifier()) {
        $this->baseField = $fcfg['key'];
        $this->collectionModelBaseRefField = $fkey;
        break;
      }
    }

    if(!$this->baseField) {
      throw new exception('EXCEPTION_MODEL_PLUGIN_COLLECTION_MISSING_BASEFIELD', exception::$ERRORLEVEL_ERROR);
    }
    if(!$this->collectionModelBaseRefField) {
      throw new exception('EXCEPTION_MODEL_PLUGIN_COLLECTION_MISSING_COLLECTIONMODEL_BASEREF_FIELD', exception::$ERRORLEVEL_ERROR);
    }
  }

  /**
   * field of the base model
   * that is used as the join counterpart
   * mostly, this should be the PKEY of the base model
   * @var string
   */
  protected $baseField = null;

  /**
   * the field of the collection model
   * that references the base model
   * @var string
   */
  protected $collectionModelBaseRefField = null;

  /**
   * returns the field name in the base model
   * we're referencing
   *
   * @return string
   */
  public function getBaseField() : string {
    return $this->baseField;
  }

  /**
   * returns the field name in the auxiliary model
   * that stores the reference to the base model
   * (-> getBaseField)
   *
   * @return string
   */
  public function getCollectionModelBaseRefField() : string {
    return $this->collectionModelBaseRefField;
  }

}
