<?php
namespace codename\core\model\schematic;

use codename\core\app;
use codename\core\model;

/**
 * A model that dynamically wraps around another model
 * to form a new, discrete model (e.g. as a subquery)
 */
class discreteDynamic extends \codename\core\model\schematic\sql
  implements \codename\core\model\discreteModelSchematicSqlInterface
{
  /**
   * the model the discrete query is relying on.
   * @var \codename\core\model\schematic\sql
   */
  protected $baseModel = null;

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(string $name, \codename\core\model\schematic\sql $model)
  {
    $this->baseModel = $model;

    // override $modeldata
    $modeldata = [];
    parent::__CONSTRUCT($modeldata);
    $this->setConfig($model->getConfig()->get('connection'), null, $name);
  }

  /**
   * Returns the underyling base model
   * @return model
   */
  public function getBaseModel(): model {
    return $this->baseModel;
  }

  /**
   * @inheritDoc
   */
  public function getTableIdentifier(
    ?string $schema = null,
    ?string $model = null
  ): string {
    if($schema || $model) {
      return parent::getTableIdentifier($schema, $model);
    } else {
      return $this->table;
    }
  }

  /**
   * @inheritDoc
   */
  public function setConfig(string $connection = null, ?string $schema, string $table) : \codename\core\model {

    $this->schema = $schema;
    $this->table = $table;

    if($connection != null) {
      $this->db = app::getDb($connection, $this->storeConnection);
    }

    $config = null;
    // $config = app::getCache()->get('MODELCONFIG_', get_class($this));
    if(is_array($config)) {
      $this->config = new \codename\core\config($config);

      // Connection now defined in model .json
      if($this->config->exists("connection")) {
        $connection = $this->config->get("connection");
      }
      $this->db = app::getDb($connection, $this->storeConnection);

      return $this;
    }

    if(!$this->config) {
      $this->config = $this->loadConfig();
    }

    // Connection now defined in model .json
    if($this->config->exists("connection")) {
      $connection = $this->config->get("connection");
    } else {
      $connection = 'default';
    }

    if(!$this->db) {
      $this->db = app::getDb($connection, $this->storeConnection);
    }

    // app::getCache()->set('MODELCONFIG_', get_class($this), $this->config->get());
    return $this;
  }

  /**
   * @inheritDoc
   */
  protected function loadConfig(): \codename\core\config
  {
    //
    // Inherit the config from the base model
    // TODO: check what's happening with nested models, though.
    //
    return new \codename\core\config($this->baseModel->getConfig()->get());
  }

  /**
   * @inheritDoc
   */
  public function getFields(): array
  {
    return static::getAliasedFieldlistRecursive($this->baseModel);
  }

  /**
   * Function that returns all fields available,
   * recursively. Calls ::getCurrentAliasedFieldlist() on every item.
   * @param  model $model               [description]
   * @return array        [description]
   */
  protected static function getAliasedFieldlistRecursive(model $model): array {
    $fields = $model->getCurrentAliasedFieldlist();
    foreach($model->getNestedJoins() as $join) {
      $fields = array_merge($fields, static::getAliasedFieldlistRecursive($join->model));
    }
    return $fields;
  }

  /**
   * @inheritDoc
   */
  protected function isDiscreteModel(): bool
  {
    return true;
  }

  /**
   * @inheritDoc
   */
  public function getDiscreteModelQuery(array &$params): string
  {
    $parentAlias = null;
    $cteName = null; // null for this subquery stuff...
    $cte = [];

    $tableUsage = [ "{$this->baseModel->schema}.{$this->baseModel->table}" => 1];
    $aliasCounter = 0;
    $deepjoin = $this->baseModel->deepJoin($this->baseModel, $tableUsage, $aliasCounter, $parentAlias, $params, $cte);

    $fieldlist = $this->baseModel->getCurrentFieldlist($cteName, $params);
    $fromQueryString = $this->baseModel->getTableIdentifier();

    $fieldQueryString = '';

    if(count($fieldlist) == 0) {
      $fieldQueryString = ' * ';
    } else {
      $fields = array();
      foreach($fieldlist as $f) {
        // schema and table specifier separator (.)(.)
        // schema.table.field (and field may be a '*')
        $fields[] = implode('.', $f);
      }
      // chain the fields
      $fieldQueryString = implode(',', $fields);
    }

    $mainAlias = null;
    if($tableUsage["{$this->baseModel->schema}.{$this->baseModel->table}"] > 1) {
      $mainAlias = $this->baseModel->getTableIdentifier();
    }

    // clean start from filters
    $query = '';

    $query .= $this->baseModel->getFilterQuery($params, $mainAlias);

    $groups = $this->baseModel->getGroups($mainAlias);
    if(count($groups) > 0) {
      $query .= ' GROUP BY '. implode(', ', $groups);
    }

    //
    // HAVING clause
    //
    $aggregate = $this->baseModel->getAggregateQueryComponents($params);
    if(count($aggregate) > 0) {
      $query .= ' HAVING '. static::convertFilterQueryArray($aggregate);
    }

    $orderQueryString = '';
    if(count($this->order) > 0) {
      $query .= $this->baseModel->getOrders($this->baseModel->order);
    }

    if(!is_null($this->limit)) {
      $query .= $this->baseModel->getLimit($this->baseModel->limit);
    }

    if(!is_null($this->offset) > 0) {
      $query .= $this->baseModel->getOffset($this->baseModel->offset);
    }

    return "(SELECT {$fieldQueryString} FROM {$fromQueryString} {$deepjoin} {$query})";
  }

  /**
   * @inheritDoc
   */
  public function reset()
  {
    $this->baseModel->reset();
    parent::reset();
  }

  /**
   * @inheritDoc
   */
  public function save(array $data): model {
    throw new \LogicException('Not implemented.');
  }

  /**
   * @inheritDoc
   */
  public function replace(array $data): model {
    throw new \LogicException('Not implemented.');
  }

  /**
   * @inheritDoc
   */
  public function update(array $data): model {
    throw new \LogicException('Not implemented.');
  }

  /**
   * @inheritDoc
   */
  public function delete($primaryKey = null): model {
    throw new \LogicException('Not implemented.');
  }
}
