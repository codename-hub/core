<?php
namespace codename\core;

use \codename\core\model\timemachineInterface;
use \codename\core\model\timemachineModelInterface;

use codename\rest\app;

/**
 * timemachine
 * provides the ability to access historic versions of model data
 * @author Kevin Dargel <kevin@jocoon.de>
 * @since 2017-03-08
 */
class timemachine {

  /**
   * @var model
   */
  protected $timemachineModel = null;

  /**
   * a model capable of using the timemachine
   * @var model
   */
  protected $capableModel = null;

  /**
   * get a timemachine instance for a given model name
   *
   * @param  string      $capableModelName [description]
   * @param  string      $app              [description]
   * @param  string      $vendor           [description]
   * @return timemachine                   [description]
   */
  public static function getInstance(string $capableModelName, string $app = '', string $vendor = '') : timemachine {
    $identifier = $capableModelName.'-'.$vendor.'-'.$app;
    return self::$instances[$identifier] ?? (self::$instances[$identifier] = new self(app::getModel($capableModelName, $app, $vendor)));
  }

  /**
   * instance cache
   * @var timemachine[]
   */
  protected static $instances = [];

  /**
   * creates a new instance of the timemachine
   * please use ::getInstance() instead!
   *
   * @param model $capableModel [description]
   */
  public function __construct(model $capableModel)
  {
    if(!$capableModel instanceof timemachineInterface) {
      throw new exception('MODEL_DOES_NOT_IMPLEMENT_TIMEMACHINE_INTERFACE', exception::$ERRORLEVEL_FATAL, $capableModel->getIdentifier());
    }
    if(!$capableModel->isTimemachineEnabled()) {
      throw new exception('MODEL_IS_NOT_TIMEMACHINE_ENABLED', exception::$ERRORLEVEL_FATAL, $capableModel->getIdentifier());
    }

    // set the source model (model capable of using the timemachine)
    $this->capableModel = $capableModel;

    // set the associated timemachine model
    // this model is used for storing the delta data
    $this->timemachineModel = $capableModel->getTimemachineModel();

    if(!($this->timemachineModel instanceof timemachineModelInterface)) {
      throw new exception('TIMEMACHINE_MODEL_DOES_NOT_IMPLEMENT_TIMEMACHINEMODELINTERFACE', exception::$ERRORLEVEL_FATAL, $this->timemachineModel->getIdentifier());
    }
  }

  /**
   * returns a dataset at a given point in time
   *
   * @param  int   $identifier [description]
   * @param  int   $timestamp  [description]
   * @return array             [description]
   */
  public function getHistoricData(int $identifier, int $timestamp) : array
  {
    $delta = $this->getDeltaData($identifier, $timestamp);
    $current = $this->getCurrentData($identifier);
    $historic = array_replace($current, $delta);
    return $historic;
  }

  /**
   * returns the fields excluded from timemachine tracking
   * @return string[]
   */
  protected function getExcludedFields() : array
  {
    // by default, exclude the primarykey
    // and both mandatory fields when using schematic models: ..._created, ..._modified
    $excludedFields = array(
      $this->capableModel->getPrimarykey(),
      $this->capableModel->getIdentifier() .'_created',
      $this->capableModel->getIdentifier() .'_modified'
    );

    // TODO: provide an interface for excluding fields via capableModel

    return $excludedFields;
  }

  /**
   * [getDeltaData description]
   * @param  int   $identifier [the primary key]
   * @param  int   $timestamp  [the oldest timestamp we're retrieving the data for]
   * @return array             [delta data]
   */
  public function getDeltaData(int $identifier, int $timestamp) : array
  {
    $history = $this->getHistory($identifier, $timestamp);
    $excludedFields = $this->getExcludedFields();

    $delta = array();
    foreach($history as $state) {
      $h = $state[$this->timemachineModel->getIdentifier() . '_data'];
      foreach($h as $key => $value) {
        if(!in_array($key, $excludedFields)) {
          if((!array_key_exists($key, $delta)) || ($data[$key] != $value)) {
            // value differs or even the key doesn't exist
            $delta[$key] = $value;
          }
        }
      }
    }
    return $delta;
  }

  /**
   * returns a history of all changes done to an entry in descending order
   * optionally, until a specific timestamp
   * @param int   $identifier [id/primary key value]
   * @param int   $timestamp [unix timestamp, default 0 for ALL/until now]
   * @return array
   */
  public function getHistory(int $identifier, int $timestamp = 0) : array
  {
    $this->timemachineModel
      ->addFilter($this->timemachineModel->getIdentifier() . '_model', $this->capableModel->getIdentifier())
      ->addFilter($this->timemachineModel->getIdentifier() . '_ref', $identifier)
      ->addOrder($this->timemachineModel->getIdentifier() . '_created', 'DESC');

    if($timestamp !== 0) {
      // return all entries newer than a specific state
      // to go through all entries in descending order
      $this->timemachineModel->addFilter($this->timemachineModel->getIdentifier() . '_created', \codename\core\helper\date::getTimestampAsDbdate($timestamp), '>=');
    }

    // get the history (all respective timemachine entries) for the requested time range
    $history = $this->timemachineModel->search()->getResult();

    // retrieve target datatypes
    $datatype = $this->capableModel->config->get('datatype');

    foreach($history as &$r) {
      foreach($r as $key => &$value) {
        if(array_key_exists($key, $datatype)) {
          if(strpos($datatype[$key], 'structu') !== false ) {
            $value = app::object2array(json_decode($value, false)/*, 512, JSON_UNESCAPED_UNICODE)*/);
          }
        }
      }
    }

    return $history;
  }

  /**
   * returns the current dataset
   *
   * @param  int   $identifier [description]
   * @return array             [description]
   */
  public function getCurrentData(int $identifier) : array
  {
    $current = $this->capableModel->load($identifier);
    return $current;
  }

  /**
   * saves the delta-based state of a given model and entry
   * and returns the respective entry id or NULL (empty delta)
   * @param  int    $identifier [description]
   * @param  array  $newData    [description]
   * @return int|null                [description]
   */
  public function saveState(int $identifier, array $newData) : ?int
  {
    $data = $this->getCurrentData($identifier);
    $delta = array();
    $excludedFields = $this->getExcludedFields();

    foreach($newData as $key => $value) {
      if(!in_array($key, $excludedFields)) {
        if((!array_key_exists($key, $data)) || ($data[$key] != $value)) {
          // value differs or even the key doesn't exist
          $delta[$key] = $data[$key] ?? null; // store EXISTING/old data (!)
        }
      }
    }

    // do not story empty deltas (no difference)
    if(count($delta) === 0) {
      return null;
    }

    if($this->timemachineModel instanceof \codename\core\model\timemachineModelInterface) {
      $this->timemachineModel->save([
        $this->timemachineModel->getModelField()  => $this->capableModel->getIdentifier(),
        $this->timemachineModel->getRefField()    => $identifier,
        $this->timemachineModel->getDataField()   => $delta
      ]);
      return $this->timemachineModel->lastInsertId();
    }
  }

}
