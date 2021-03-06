<?php
namespace codename\core\model\schemeless;
use \codename\core\model;
use \codename\core\app;

/**
 * dynamic model
 * readonly?
 */
class dynamic extends \codename\core\model\schemeless implements \codename\core\model\modelInterface {

  /**
   * Contains the driver to use for this model and the plugins
   * @var string $type
   */
  CONST DB_TYPE = 'dynamic';

  /**
   * I contain the name of the model to use
   * @var string $name
   */
  protected $name = '';

  /**
   * I contain the prefix of the model to use
   * @var string $prefix
   */
  protected $prefix = '';

  /**
   * Creates an instance
   * @param array $modeldata [e.g. app => appname]
   * @return model
   * @todo refactor the constructor for no method args
   */
  public function __CONSTRUCT(array $modeldata = array()) {
      parent::__CONSTRUCT($modeldata);
      $this->errorstack = new \codename\core\errorstack('VALIDATION');
      $this->appname = $this->modeldata->get('app') ?? app::getApp();
      return $this;
  }

  /**
   * [setConfig description]
   * @param  string               $file [data source file, .json]
   * @param  string               $name [model name for getting the config itself]
   * @return model                [description]
   */
  public function setConfig(string $prefix, string $name, array $config = null) : model {
    $this->prefix = $prefix;
    $this->name = $name;
    $this->config = $config ?? $this->loadConfig();
    return $this;
  }

  /**
   * loads a new config file (uncached)
   * @return \codename\core\config
   */
  protected function loadConfig() : \codename\core\config {
    if($this->modeldata->exists('appstack')) {
      return new \codename\core\config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true, false, $this->modeldata->get('appstack'));
    } else {
      return new \codename\core\config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true);
    }
  }

  /**
   * @inheritDoc
   */
  public function getIdentifier() : string
  {
    return $this->name;
  }

  /**
   * @inheritDoc
   */
  public function search() : model
  {
    return $this;
  }

  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    return;
  }

  /**
   * @inheritDoc
   */
  public function delete($primaryKey = null) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function save(array $data) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function copy($primaryKey) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  protected function internalGetResult(): array
  {
    return $this->doQuery('');
  }

  /**
   * @inheritDoc
   */
  protected function compatibleJoin(\codename\core\model $model): bool
  {
    return false; // ?
  }

  /**
   * @inheritDoc
   */
  protected function doQuery(string $query, array $params = array())
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * [filterResults description]
   * @param  array $data [description]
   * @return array       [description]
   */
  protected function filterResults(array $data) : array {
      throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * [mapResults description]
   * @param  array $data [description]
   * @return array       [description]
   */
  protected function mapResults(array $data) : array {
      return $data;
  }

  /**
   * @inheritDoc
   */
  public function withFlag(int $flagval) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function withoutFlag(int $flagval) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function withDefaultFlag(int $flagval) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function withoutDefaultFlag(int $flagval) : model
  {
    throw new \LogicException('Not implemented'); // TODO
  }
}
