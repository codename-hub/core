<?php
namespace codename\core\model\plugin;

/**
 * Provide many to many relationship functionality as a plugin
 * @package core
 * @since 2018-01-08
 */
abstract class collection extends \codename\core\model\plugin {

  /**
   * the original model (base)
   * this model's result gets extended by the collection data
   * @var \codename\core\model
   */
  public $baseModel = null;

  /**
   * auxiliary (helper) model
   * @var \codename\core\model
   */
  public $auxModel = null;

  /**
   * the referenced model
   * @var \codename\core\model
   */
  public $refModel = null;

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
   * @param \codename\core\model $auxModel
   * @param \codename\core\model $refModel
   */
  public function __construct(\codename\core\value\text\modelfield $field, \codename\core\model $baseModel, \codename\core\model $auxModel, \codename\core\model $refModel) {
    $this->field = $field;
    $this->baseModel = $baseModel;
    $this->auxModel = $auxModel;
    $this->refModel = $refModel;

    // prepare some data
    foreach($this->auxModel->config->get('foreign') as $fkey => $fcfg) {
      if($fcfg['model'] == $this->baseModel->getIdentifier()) {
        $this->baseField = $fcfg['key'];
        $this->auxBaseField = $fkey;
      }
    }

    foreach($this->auxModel->config->get('foreign') as $fkey => $fcfg) {
      if($fcfg['model'] == $this->refModel->getIdentifier()) {
        $this->refField = $fcfg['key'];
        $this->auxRefField = $fkey;
      }
    }

    // construct this model
    $this->model = $this->auxModel->addModel($this->refModel);
  }

  protected $baseField = null;
  protected $auxBaseField = null;

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
  public function getAuxBaseField() : string {
    return $this->auxBaseField;
  }

  protected $refField = null;
  protected $auxRefField = null;

  /**
   * returns the field name in the foreign (ref/referenced) model
   * usually, this should be the primary key.
   *
   * @return string
   */
  public function getRefField() : string {
    return $this->refField;
  }

  /**
   * returns the field name in the auxiliary model
   * that stores the reference to the foreign (ref/referenced) model
   * (-> getRefField)
   *
   * @return string
   */
  public function getAuxRefField() : string {
    return $this->auxRefField;
  }

  /**
   * Undocumented variable
   *
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * returns the model specific for this collection plugin
   *
   * @return \codename\core\model
   */
  public function getModel() : \codename\core\model {
    return $this->model;
  }

}