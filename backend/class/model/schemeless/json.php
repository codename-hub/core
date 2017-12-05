<?php
namespace codename\core\model\schemeless;
use \codename\core\model;

/**
 * model for a json data source (json array)
 * readonly?
 */
abstract class json extends \codename\core\model\schemeless implements \codename\core\model\modelInterface {

  /**
   * Contains the driver to use for this model and the plugins
   * @var string $type
   */
  CONST DB_TYPE = 'json';

  /**
   * I contain the path to the XML file that is used
   * @var string $file
   */
  protected $file = '';

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
  public function __CONSTRUCT(array $modeldata) {
      $this->errorstack = new \codename\core\errorstack('VALIDATION');
      $this->appname = $modeldata['app'];
      return $this;
  }

  /**
   * [setConfig description]
   * @param  string               $file [data source file, .json]
   * @param  string               $name [model name for getting the config itself]
   * @return model                [description]
   */
  public function setConfig(string $file = null, string $prefix, string $name) : model {
    $this->file = $file;
    $this->prefix = $prefix;
    $this->name = $name;
    $this->config = $this->loadConfig();
    return $this;
  }

  /**
   * loads a new config file (uncached)
   * @return \codename\core\config
   */
  protected function loadConfig() : \codename\core\config {
    return new \codename\core\config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true);
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
  protected function doQuery(string $query, array $params = array())
  {
    // do not inherit and do not traverse appstack
    $data = (new \codename\core\config\json($this->file))->get();

    if(count($this->filter) > 0) {
        $data = $this->filterResults($data);
    }

    return $this->mapResults($data);
  }

  /**
   * [filterResults description]
   * @param  array $data [description]
   * @return array       [description]
   */
  protected function filterResults(array $data) : array {
      $filteredData = array();
      foreach($data as $entry) {
          $pass = true;
          foreach($this->filter as $filter) {
              if(!$pass) {
                  continue;
              }

              if($filter instanceof \codename\core\model\plugin\filter\executableFilterInterface) {
                if(!$filter->matches($entry)) {
                  $pass = false;
                  continue;
                }
              } else {
                // we may warn for incompatible filters?
              }
          }
          if(!$pass) {
              continue;
          }
          $filteredData[] = $entry;
      }
      return $filteredData;
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