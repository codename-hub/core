<?php
namespace codename\core\model\schemeless;
use \codename\core\model;
use \codename\core\app;

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
  public $prefix = '';

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
    $this->doQuery('');
    return $this;
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
    return $this->result;
  }

  /**
   * [protected description]
   * @var array
   */
  protected static $t_data = [];

  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    $identifier = $this->file . '_' . ($this->modeldata->exists('appstack') ? '1' : '0');
    if(!isset(self::$t_data[$identifier])) {
      if($this->modeldata->exists('appstack')) {
        // traverse (custom) appstack, if we defined it
        self::$t_data[$identifier] = (new \codename\core\config\json($this->file, true, false, $this->modeldata->get('appstack')))->get();
      } else {
        self::$t_data[$identifier] = (new \codename\core\config\json($this->file))->get();
      }

      // map PKEY (index) to a real field
      $pkey = $this->getPrimaryKey();
      array_walk(self::$t_data[$identifier], function(&$item, $key) use ($pkey) {
        if(!isset($item[$pkey])) {
          $item[$pkey] = $key;
        }
      });
    }

    $data = self::$t_data[$identifier];

    if(count($this->virtualFields) > 0) {
      foreach($data as &$d) {
        foreach($this->virtualFields as $field => $function) {
          $d[$field] = $function($d);
        }
      }
    }

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
      //
      // special hack
      // to highly speed up filtering for json/array key filtering
      //
      if(count($this->filter) === 1) {
        foreach($this->filter as $filter) {
          if($filter->field->get() == $this->getPrimarykey() && $filter->operator == '=') {
            $data = isset($data[$filter->value]) ? [$data[$filter->value]] : [];
          }
        }
      }
      if(count($this->filter) >= 1) {
        $filteredData = array_filter($data, function($entry) {
          $pass = null;
          foreach($this->filter as $filter) {
              if($pass === false && $filter->conjunction === 'AND') {
                  continue;
              }

              if($filter instanceof \codename\core\model\plugin\filter\executableFilterInterface) {
                if($pass === null) {
                  $pass = $filter->matches($entry);
                } else {
                  if($filter->conjunction === 'OR') {
                    $pass = $pass || $filter->matches($entry);
                  } else {
                    $pass = $pass && $filter->matches($entry);
                  }
                }
              } else {
                // we may warn for incompatible filters?
              }
          }

          //
          // NOTE/TODO: What to do, when pass === null ?
          //
          return $pass;
        });
      }
      return array_values($filteredData);
  }

  /**
   * @inheritDoc
   */
   protected function compatibleJoin(\codename\core\model $model) : bool
   {
     return false;
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
