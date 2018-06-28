<?php
namespace codename\core\model\schematic;
use \codename\core\app;
use \codename\core\exception;

/**
 * base SQL specific SQL commands
 * @package core
 * @author Kevin Dargel
 * @since 2017-03-01
 */
abstract class sql extends \codename\core\model\schematic implements \codename\core\model\modelInterface, \codename\core\model\virtualFieldResultInterface, \codename\core\transaction\transactionableInterface {

    /**
     * invalid foreign key config during deepJoin()
     * @var string
     */
    CONST EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG = "EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG";

    /**
     * The search method will use this as the filter operator
     * @var string $filterOperator
     */
    protected $filterOperator = ' AND ';

    /**
     * config option that configures database connection (PDO) storage factor
     * @var bool
     */
    protected $storeConnection = true;

    /**
     * Creates and configures the instance of the model. Fallback connection is 'default' database
     * @param string|null $connection  [Name of the connection in the app configuration file]
     * @param string $schema      [Schema to use the model for]
     * @param string $table       [Table to use the model on]
     * @return \codename\core\model
     */
    public function setConfig(string $connection = null, string $schema, string $table) : \codename\core\model {

        $this->schema = $schema;
        $this->table = $table;

        if($connection != null) {
        	$this->db = app::getDb($connection, $this->storeConnection);
        }

        $config = app::getCache()->get('MODELCONFIG_', get_class($this));
        if(is_array($config)) {
            $this->config = new \codename\core\config($config);

            // Connection now defined in model .json
            if($this->config->exists("connection")) {
            	$connection = $this->config->get("connection");
            }
            $this->db = app::getDb($connection, $this->storeConnection);

            return $this;
        }

        $this->config = $this->loadConfig();

        // Connection now defined in model .json
        if($this->config->exists("connection")) {
        	$connection = $this->config->get("connection");
        } else {
        	$connection = 'default';
        }

        $this->db = app::getDb($connection, $this->storeConnection);

        if(!in_array("{$this->table}_created", $this->config->get("field"))) {
           throw new exception('EXCEPTION_MODEL_CONFIG_MISSING_FIELD', exception::$ERRORLEVEL_FATAL, "{$this->table}_created");
        }
        if(!in_array("{$this->table}_modified", $this->config->get("field"))) {
           throw new exception('EXCEPTION_MODEL_CONFIG_MISSING_FIELD', exception::$ERRORLEVEL_FATAL, "{$this->table}_modified");
        }

        app::getCache()->set('MODELCONFIG_', get_class($this), $this->config->get());
        return $this;
    }

    /**
     * Exception thrown when a model is missing a field that is required by the framework
     * (e.g. _created and/or _modified)
     * @var string
     */
    const EXCEPTION_MODEL_CONFIG_MISSING_FIELD = 'EXCEPTION_MODEL_CONFIG_MISSING_FIELD';

    /**
     * loads a new config file (uncached)
     * @return \codename\core\config
     */
    protected function loadConfig() : \codename\core\config {
      return new \codename\core\config\json('config/model/' . $this->schema . '_' . $this->table . '.json', true);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::getResult()
     */
    /*public function getResult() : array {
        $result = $this->result;

        if (is_null($result)) {
            $this->result = $this->internalGetResult();
            $result = $this->result;
        }

        $result = $this->normalizeResult($result);
        $this->data = new \codename\core\datacontainer($result);
        return $this->data->getData();
    }*/


    /**
     * Undocumented variable
     *
     * @var \codename\core\model\plugin\collection[]
     */
    protected $collectionFields = [];

    /**
     * Undocumented function
     *
     * @param string $field
     * @return \codename\core\model
     */
    public function addCollectionField(string $field) : \codename\core\model {
      $modelfield = \codename\core\value\text\modelfield::getInstance($field);
      // if($this->fieldExists($modelfield)) {

        $cfg = $this->config->get('collection>'.$modelfield->get());

        if($cfg) {
          if($cfg['manytomany'] && $cfg['aux']) {
            // $this->collectionFields[] =

            $aux = $cfg['aux'];
            $auxModel = app::getModel($aux['model'], $aux['app'] ?? '');
            $refModel = app::getModel($cfg['model'], $cfg['app'] ?? '');

            $class = '\\codename\\core\\model\\plugin\\collection\\' . $this->getType();
            array_push($this->collectionFields, new $class($modelfield, $this, $auxModel, $refModel));
            return $this;
          }
        }
      // }
      // return $this;
      die("err");
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getResultWithCollections() : array {

      $result = $this->getResult();

      if(count($this->collectionFields) > 0) {
        foreach($this->collectionFields as $collectionField) {

          // prepare the collection model for querying
          $collectionModel = $collectionField->getModel();

          // perform the plugin on each result row
          foreach($result as &$r) {

            // check if $r[$baseField] == null !!
            $collectionResult = $collectionModel
              // ->addField($collectionField->getAuxRefField()) // ??
              ->addFilter($collectionField->getAuxBaseField(), $r[$collectionField->getBaseField()])
              ->search()->getResult();

            // map collection result to singular/scalar values
            $collectionMapped = array_map(function($cr) use ($collectionField) {
              return $cr[$collectionField->getAuxRefField()];
            }, $collectionResult);

            // put collection result in final result array element
            $r[$collectionField->field->get()] = $collectionMapped;
          }
        }
      }

      return $result;
    }

    /**
     * Undocumented function
     *
     * ... or not?
     * This saves the dataset and children
     * - present in the configuration
     * - present in the current dataset as a sub-array (named field)
     *
     *
     * @param array $data
     * @return \codename\core\model
     */
    public function saveWithChildren(array $data) : \codename\core\model {

      // Open a virtual transaction
      // as we might do some multi-model saving
      $this->db->beginVirtualTransaction();

      $data2 = $data;
      // unset all collection fields for this to work
      if(count($this->collectionFields) > 0) {
        foreach($this->collectionFields as $collectionField) {
          unset($data2[$collectionField->field->get()]);
        }
      }

      // save children
      if($this->config->exists('children')) {
        foreach($this->config->get('children') as $child => $childConfig) {

          // get the nested models / join plugin instances
          $foreignConfig = $this->config->get('foreign>'.$childConfig['field']);
          $field = $childConfig['field'];

          // get the join plugin valid for the child reference field
          $res = array_filter($this->getNestedJoins(), function(\codename\core\model\plugin\join $join) use ($field) {
            return $join->modelField == $field;
          });

          if(count($res) === 1) {
            // NOTE: array_filter preserves keys. use array_values to simply use index 0
            // TODO: check for required fields...
            if(isset($data[$child])) {
              $model = array_values($res)[0]->model;
              $model->saveWithChildren($data[$child]);
              // if we just inserted a NEW entry, get its primary key and save into the root model
              if(empty($data[$child][$model->getPrimaryKey()])) {
                $data2[$childConfig['field']] = $model->lastInsertId();
              }
            }
          } else {
            // error?
            // Throw an exception if there is no single, but multiple joins that match our condition
            if(count($res) > 1) {
              throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_CHILDREN_AMBIGUOUS_JOINS', exception::$ERRORLEVEL_ERROR, [
                'child' => $child,
                'childConfig' => $childConfig,
                'foreign' => $field,
                'foreignConfig' => $foreignConfig
              ]);
            }

            // TODO: make sure we should do it like that.
            //
          }
          unset($data2[$child]);
        }
      }
      // end save children


      $this->save($data2);

      $update = (array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0);

      if(!$update) {
        $data[$this->getPrimarykey()] = $this->lastInsertId();
      }

      if(count($this->collectionFields) > 0) {
        foreach($this->collectionFields as $collectionField) {

          $collectionResult = [];
          $existing = [];

          if($update) {
            $collectionResult = $collectionField->getModel()
              ->addFilter($collectionField->getAuxBaseField(), $data[$collectionField->getBaseField()])
              ->search()->getResult();

            $existing = array_map(function($cr) use ($collectionField) {
              return $cr[$collectionField->getAuxRefField()];
            }, $collectionResult);
          }

          // get all unchanged values
          // by calculating the intersection of existing and to-be-saved values
          $unchanged = array_intersect($existing, $data[$collectionField->field->get()]);

          $create = array_diff($data[$collectionField->field->get()], $existing);
          $delete = array_diff($existing, $data[$collectionField->field->get()]);

          foreach($collectionResult as $v) {
            if(in_array($v[$collectionField->getAuxRefField()], $delete)) {
              $collectionField->getModel()->delete($v[$collectionField->getModel()->getPrimaryKey()]);
              continue;
            }
          }

          foreach($create as $v) {
            $collectionField->auxModel->save([
              $collectionField->getAuxBaseField() => $data[$collectionField->getBaseField()],
              $collectionField->getAuxRefField() => $v
            ]);
          }


        }
      }

      // end the virtual transaction
      // if this is the last (outer) model to call save()/saveWithChildren()
      // this closes the pending transaction on db/connection-level
      $this->db->endVirtualTransaction();

      return $this;
    }

    /**
     * @inheritDoc
     */
    protected function internalQuery(string $query, array $params = array()) {
      // perform internal query
      return $this->db->query($query, $params);
    }

    /**
     * @inheritDoc
     */
    protected function internalGetResult(): array
    {
      $result = $this->db->getResult();
      if($this->virtualFieldResult) {
        $result = $this->getVirtualFieldResult($result);

        //
        // Root element virtual fields
        //
        if(count($this->virtualFields) > 0) {
          foreach($result as &$d) {
            $d = $this->handleVirtualFields($d);
          }
        }
      }
      return $result;
    }

    /**
     * @inheritDoc
     */
    public function setVirtualFieldResult(bool $state) : \codename\core\model
    {
      $this->virtualFieldResult = $state;
      return $this;
    }

    /**
     * State of the virtual field handling
     * Decides whether to construct virtual fields (e.g. children results)
     * and put them into the result
     *
     * Needs PDO to fetch via FETCH_NAMED
     * to get distinct values for joined models
     *
     * @var bool
     */
    protected $virtualFieldResult = false;

    /**
     * [getVirtualFieldResult description]
     * @param  array  $result     [description]
     * @param  array  $track      [description]
     * @param  array  $structure
     * @return [type]             [description]
     */
    public function getVirtualFieldResult(array $result, &$track = [], array $structure = []) {

      // app::getResponse()->setData('structure', array_merge(app::getResponse()->getData('structure') ?? [], [$structure]));

      foreach($this->getNestedJoins() as $join) {
        $track[$join->model->getIdentifier()][] = $join->model;
        if($join->model instanceof \codename\core\model\virtualFieldResultInterface) {
          $result = $join->model->getVirtualFieldResult($result, $track, array_merge($structure, [$join->modelField]) );
        }
      }
      foreach($this->getSiblingJoins() as $join) {
        $track[$join->model->getIdentifier()][] = $join->model;
        if($join->model instanceof \codename\core\model\virtualFieldResultInterface) {
          $result = $join->model->getVirtualFieldResult($result, $track, array_merge($structure, [$join->modelField]) );
        }
      }

      if($this->config->exists('children')) {
        foreach($this->config->get('children') as $field => $config) {
          if($config['type'] === 'foreign') {
            $foreign = $this->config->get('foreign>'.$config['field']);
            if($this->config->get('datatype>'.$field) == 'virtual') {
              if(!isset($track[$foreign['model']])) {
                $track[$foreign['model']] = [];
              }
              // $index = count($track[$foreign['model']])-1;
              $index = null;
              foreach($this->getNestedJoins() as $join) {
                if($join->modelField === $config['field']) {
                  if(count($indexes = array_keys($track[$foreign['model']], $join->model, true)) === 1) {
                    $index = $indexes[0];
                  }
                }
              }

              if($index === null) {
                // index is still null -> model not found in currently nested models
                continue;
                // throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_VIRTUALFIELDRESULT_INDEX_UNDETERMINABLE_JOIN_NOT_FOUND', exception::$ERRORLEVEL_ERROR, [
                //   'child' => $field,
                //   'model' => $foreign['model'],
                //   'field' => $config['field']
                // ]);
              }
              $vModel = count($track[$foreign['model']]) > 0 ? $track[$foreign['model']][$index] : null;

              // app::getResponse()->setData('fieldvModelIndex>'.$field, $index);

              foreach($result as &$dataset) {
                if($vModel != null) {
                  $vData = [];
                  foreach($vModel->getFields() as $modelField) {
                    if(isset($dataset[$modelField])) {
                      if(is_array($dataset[$modelField]) && $vModel->getFieldtype(\codename\core\value\text\modelfield::getInstance($modelField)) !== 'virtual') {
                        $vData[$modelField] = $dataset[$modelField][$index] ?? null;
                      } else {
                        $vData[$modelField] = $dataset[$modelField] ?? null;
                      }
                      // if($vData[$modelField] === null) {
                      //   app::getResponse()->setData('vModelModelFieldIsNull>'.$this->getIdentifier(), [$foreign['model'], $index, $modelField, $dataset]);
                      // }
                    }
                  }

                  $vData = $vModel->normalizeRow($vData);

                  // handle custom virtual fields
                  if(count($vModel->getVirtualFields()) > 0) {
                    // foreach($vData as &$d) {
                    $vData = $vModel->handleVirtualFields($vData);
                    // }
                  }

                  $dataset[$field] = $vData;
                } else {
                  // app::getResponse()->setData('vModelIsNull>'.$this->getIdentifier(), [$foreign['model'], $index]);
                  $dataset[$field] = null;
                }
              }
            }
          }

          // TODO: Handle collections?
        }
      }


      // handle custom virtual fields
      // if(count($this->virtualFields) > 0) {
      //   foreach($result as &$d) {
      //     $d = $this->handleVirtualFields($d);
      //   }
      // }
      // if(count($this->virtualFields) > 0) {
      //   app::getResponse()->setData('protocol_schematic_sql_internalGetResult>'.$this->getIdentifier(), $this->virtualFields);
      //   foreach($result as &$d) {
      //     $d = $this->handleVirtualFields($d);
      //   }
      // }
      return $result;
    }

    /**
     * the current database connection instance
     * @return \codename\core\database [description]
     */
    public function getConnection(): \codename\core\database
    {
      return $this->db;
    }

    /**
     * Use right joining for this model
     * which allows empty joined fields to appear
     * @var bool
     */
    public $rightJoin = false;

    /**
     * @inheritDoc
     */
    protected function compatibleJoin(\codename\core\model $model): bool
    {
      return parent::compatibleJoin($model) && ($this->db == $model->db);
    }

    /**
     * [deepJoin description]
     * @param  \codename\core\model   $model          [model currently worked-on]
     * @param  array                  &$tableUsage    [table usage as reference]
     * @param  int                    &$aliasCounter  [alias counter as reference]
     * @return string                 [query part]
     */
    public function deepJoin(\codename\core\model $model, array &$tableUsage = array(), int &$aliasCounter = 0, string $parentAlias = null) {
        if(count($model->getNestedJoins()) == 0 && count($model->getSiblingJoins()) == 0) {
            return '';
        }
        $ret = '';

        // Loop through nested (children/parents)
        foreach($model->getNestedJoins() as $join) {
            $nest = $join->model;

            // check model joining compatible
            if(!$model->compatibleJoin($nest)) {
              continue;
            }

            if(array_key_exists("{$nest->schema}.{$nest->table}", $tableUsage)) {
              $aliasCounter++;
              $tableUsage["{$nest->schema}.{$nest->table}"]++;
              $alias = "a".$aliasCounter;
              $aliasAs = "AS ".$alias;
            } else {
              $tableUsage["{$nest->schema}.{$nest->table}"] = 1;
              $aliasAs = '';
              $alias = "{$nest->schema}.{$nest->table}";
            }

            // get join method from plugin
            $joinMethod = $join->getJoinMethod();

            // if $joinMethod == null == DEFAULT -> use current config.
            // this should be deprecated or removed...
            if($joinMethod == null) {
              $joinMethod = "LEFT JOIN";
              if($this->rightJoin) {
                $joinMethod = "RIGHT JOIN";
              }
            }

            // find the correct KEY/field in the current model (do not simply join PKEY on PKEY (names))
            /* $thisKey = null;
            $joinKey = null;
            foreach($this->config->get('foreign') as $fkeyName => $fkeyConfig) {
              if($fkeyConfig['table'] == $nest->table) {
                $thisKey = $fkeyName;
                $joinKey = $fkeyConfig['key'];
                break;
              }
            }

            // Reverse Join
            if(($thisKey == null) || ($joinKey == null)) {
              foreach($nest->config->get('foreign') as $fkeyName => $fkeyConfig) {
                if($fkeyConfig['table'] == $this->table) {
                  $thisKey = $fkeyConfig['key'];
                  $joinKey = $fkeyName;
                  break;
                }
              }
            }*/

            $thisKey = $join->modelField;
            $joinKey = $join->referenceField;

            if(($thisKey == null) || ($joinKey == null)) {
              throw new \codename\core\exception(self::EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG, \codename\core\exception::$ERRORLEVEL_FATAL, array($this->table, $nest->table));
            }

            $joinComponents = [];

            $useAlias = $parentAlias ?? $this->table;

            if(is_array($thisKey) && is_array($joinKey)) {
              // TODO: check for equal array item counts! otherwise: exception
              // perform a multi-component join
              foreach($thisKey as $index => $thisKeyValue) {
                $joinComponents[] = "{$alias}.{$joinKey[$index]} = {$useAlias}.{$thisKeyValue}";
              }
            } else if(is_array($thisKey) && !is_array($joinKey)) {
              foreach($thisKey as $index => $thisKeyValue) {
                $joinComponents[] = "{$alias}.{$index} = {$useAlias}.{$thisKeyValue}";
              }
            } else if(!is_array($thisKey) && is_array($joinKey)) {
              throw new \LogicException('Not implemented multi-component foreign key join');
            } else {
              $joinComponents[] = "{$alias}.{$joinKey} = {$useAlias}.{$thisKey}";
            }

            // add conditions!
            foreach($join->conditions as $filter) {
              $operator = $filter['value'] == null ? ($filter['operator'] == '!=' ? 'IS NOT' : 'IS') : $filter['operator'];
              $value = $filter['value'] == null ? 'NULL' : $filter['value'];
              $joinComponents[] = "{$useAlias}.{$filter['field']} {$operator} {$value}";
            }

            $joinComponentsString = implode(' AND ', $joinComponents);
            $ret .= " {$joinMethod} {$nest->schema}.{$nest->table} {$aliasAs} ON $joinComponentsString";

            $join->currentAlias = $alias;

            $ret .= $nest->deepJoin($nest, $tableUsage, $aliasCounter, $join->currentAlias);
        }

        // Loop through siblings
        foreach($model->getSiblingJoins() as $join) {

          // workaround
          $sibling = $join->model;

          // check model joining compatible
          if(!$model->compatibleJoin($sibling)) {
            continue;
          }

          if(array_key_exists("{$sibling->schema}.{$sibling->table}", $tableUsage)) {
            $aliasCounter++;
            $tableUsage["{$sibling->schema}.{$sibling->table}"]++;
            $alias = "a".$aliasCounter;
            $aliasAs = "AS ".$alias;
          } else {
            $tableUsage["{$sibling->schema}.{$sibling->table}"] = 1;
            $aliasAs = '';
            $alias = "{$sibling->schema}.{$sibling->table}";
          }

          // get join method from plugin
          $joinMethod = $join->getJoinMethod();

          // if $joinMethod == null == DEFAULT -> use current config.
          // this should be deprecated or removed...
          if($joinMethod == null) {
            $joinMethod = "LEFT JOIN";
            if($this->rightJoin) {
              $joinMethod = "RIGHT JOIN";
            }
          }

          $joinMethod = $join->getJoinMethod();

          // $siblingConfig = $model->siblingJoins[$sibling->schema.'.'.$sibling->table];
          // turned upside-down (see above)
          $thisField = $join->modelField; // $siblingConfig['this_field'];
          $siblingField = $join->referenceField; // $siblingConfig['sibling_field'];

          $ret .= " {$joinMethod} {$sibling->schema}.{$sibling->table} {$aliasAs} ON {$alias}.{$siblingField} = {$this->table}.{$thisField}";
          $ret .= $sibling->deepJoin($sibling, $tableUsage, $aliasCounter);
        }
        return $ret;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::search()
     */
    public function search() : \codename\core\model {

        if($this->filterDuplicates) {
          $query = "SELECT DISTINCT ";
        } else {
          $query = "SELECT ";
        }

        // first: deepJoin to get correct alias names
        $deepjoin = $this->deepJoin($this);

        // retrieve a list of all model field lists, recursively
        // respecting hidden fields and duplicate field names in other models/tables
        $fieldlist = $this->getCurrentFieldlist();

        if(count($fieldlist) == 0) {
            $query .= ' * ';
        } else {
            $fields = array();
            foreach($fieldlist as $f) {
              // schema and table specifier separator (.)(.)
              // schema.table.field (and field may be a '*')
              $fields[] = implode('.', $f);
            }
            // chain the fields
            $query .= implode(',', $fields);
        }

        $query .= ' FROM ' . $this->schema . '.' . $this->table . ' ';

        // append the previously constructed deepjoin string
        $query .= $deepjoin;

        // prepare an array for values to submit as PDO statement parameters
        // done by-ref, so the values are arriving right here after
        // running getFilterQuery()
        $params = array();

        $query .= $this->getFilterQuery($params);

        if(count($this->group) > 0) {
            $query .= $this->getGroups($this->group);
        }

        if(count($this->order) > 0) {
            $query .= $this->getOrders($this->order);
        }

        if(!is_null($this->limit)) {
            $query .= $this->getLimit($this->limit);
        }

        if(!is_null($this->offset) > 0) {
            $query .= $this->getOffset($this->offset);
        }

        $this->doQuery($query, $params);

        return $this;
    }



    /**
     * returns a query that performs a save using UPDATE
     * (e.g. we have an existing entry that needs to be updated)
     * @param  array  $data   [data]
     * @param  array  &$param [reference array that keeps track of PDO variable names]
     * @return string         [query]
     */
    protected function saveUpdate(array $data, array &$param = array()) {
        $this->saveLog('UPDATE', $data);
        $cacheGroup = $this->getCachegroup();
        $cacheKey = "PRIMARY_" . $data[$this->getPrimarykey()];
        $this->clearCache($cacheGroup, $cacheKey);

        $query = 'UPDATE ' . $this->schema . '.' . $this->table .' SET ';
        $index = 0;
        foreach ($this->config->get('field') as $field) {
            if(in_array($field, array($this->getPrimarykey(), $this->table . "_modified", $this->table . "_created"))) {
                continue;
            }

            // If it exists, set the field
            if(array_key_exists($field, $data)) {

                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }

                if($index > 0) {
                    $query .= ', ';
                }

                $index++;

                $var = $this->getStatementVariable($param, $field);

                // performance hack: store modelfield instance!
                if(!isset($this->modelfieldInstance[$field])) {
                  $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                $query .= $field . ' = ' . ':'.$var;
            }
        }

        $var = $this->getStatementVariable($param, $this->getPrimarykey());
        $param[$var] = $this->getParametrizedValue($data[$this->getPrimarykey()], 'number_natural'); // ? hardcoded type?

        $query .= " , " . $this->table . "_modified = now() WHERE " . $this->getPrimarykey() . " = " . ':'.$var;
        return $query;

    }

    /**
     * [protected description]
     * @var \codename\core\value\text\modelfield[]
     */
    protected $modelfieldInstance = [];

    /**
     * returns a query that performs a save using INSERT
     * @param  array  $data   [data]
     * @param  array  &$param [reference array that keeps track of PDO variable names]
     * @return string         [query]
     */
    protected function saveCreate(array $data, array &$param = array(), bool $replace = false) {
        $this->saveLog('CREATE', $data);
        $query = 'INSERT INTO ' . $this->schema . '.' . $this->table .' ';
        $query .= ' (';
        $index = 0;
        foreach ($this->config->get('field') as $field) {
            if($field == $this->getPrimarykey() || in_array($field, array($this->table . "_modified", $this->table . "_created"))) {
                continue;
            }
            if(array_key_exists($field, $data)) {
                if($index > 0) {
                    $query .= ', ';
                }
                $index++;
                $query .= $field;
            }
        }
        $query .= ') VALUES (';
        $index = 0;
        foreach ($this->config->get('field') as $field) {
            if($field == $this->getPrimarykey() || in_array($field, array($this->table . "_modified", $this->table . "_created"))) {
                continue;
            }
            if(array_key_exists($field, $data)) {
                if($index > 0) {
                    $query .= ', ';
                }

                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }
                $index++;

                $var = $this->getStatementVariable($param, $field);

                // performance hack: store modelfield instance!
                if(!isset($this->modelfieldInstance[$field])) {
                  $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));

                $query .= ':'.$var;
            }
        }
        $query .= " )";
        if($replace) {
          $query .= ' ON DUPLICATE KEY UPDATE ';
          $parts = [];
          foreach ($this->config->get('field') as $field) {
              if($field == $this->getPrimarykey() || in_array($field, array($this->table . "_modified", $this->table . "_created"))) {
                  continue;
              }
              if(array_key_exists($field, $data)) {
                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }

                $var = $this->getStatementVariable($param, $field);

                // performance hack: store modelfield instance!
                if(!isset($this->modelfieldInstance[$field])) {
                  $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                $parts[] = $field . ' = ' . ':'.$var;
              }
          }
          $query .= implode(',', $parts);
        }
        $query .= ";";
        return $query;
    }

    /**
     * get a parametrized value (array)
     * for use with PDO
     * @param  mixed $value      [description]
     * @param  string $fieldtype [description]
     * @return array             [description]
     */
    protected function getParametrizedValue($value, string $fieldtype) : array {
      if(is_null($value)) {
        $param = \PDO::PARAM_NULL; // Explicit NULL
      } else {
        if($fieldtype == 'number') {
          $value = (float) $value;
          $param = \PDO::PARAM_STR; // explicitly use this one...
        } else if($fieldtype == 'number_natural') {
          $param = \PDO::PARAM_INT;
        } else if($fieldtype == 'boolean') {
          $param = \PDO::PARAM_BOOL;
        } else {
          $param = \PDO::PARAM_STR; // Fallback
        }
      }
      return array(
        $value,
        $param
      );
    }

    /**
     * json_encode wrapper
     * for customizing the output sent to the database
     * Reason: pgsql is handling the encoding for itself
     * but MySQL is doing strict encoding handling
     * @see http://stackoverflow.com/questions/4782319/php-json-encode-utf8-char-problem-mysql
     * and esp. @see http://stackoverflow.com/questions/4782319/php-json-encode-utf8-char-problem-mysql/37353316#37353316
     *
     * @param array|object   $data [or even an object?]
     * @return string   [json-encoded string]
     */
    protected function jsonEncode($data) : string {
      return json_encode($data);
    }

    /**
     * [saveLog description]
     * @param  string $mode [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    protected function saveLog(string $mode, array $data) {
        if(strpos(get_class($this), 'activitystream') == false) {
            app::writeActivity("MODEL_" . $mode, get_class($this), $data);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\modelInterface::save($data)
     *
     * [save description]
     * @param  array                  $data [description]
     * @return \codename\core\model         [description]
     */
    public function save(array $data) : \codename\core\model {
        $params = array();
        if (array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0) {
            $query = $this->saveUpdate($data, $params);
            $this->doQuery($query, $params);
        } else {
            $query = $this->saveCreate($data, $params);
            $this->cachedLastInsertId = null;
            $this->doQuery($query, $params);
            $this->cachedLastInsertId = $this->db->lastInsertId();
        }
        return $this;
    }

    /**
     * performs a create or replace (update)
     * @param  array                  $data [description]
     * @return \codename\core\model   [this instance]
     */
    public function replace(array $data) {
      $params = [];
      $query = $this->saveCreate($data, $params, true); // saveCreate with $replace = true
      $this->doQuery($query, $params);
      return $this;
    }

    /**
     * performs an update using the current filters and a given data array
     * @param  array                  $data  [description]
     * @return \codename\core\model          [this instance]
     */
    public function update(array $data) {
      if(count($this->filter) == 0) {
          throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED', exception::$ERRORLEVEL_FATAL);
      }
      $query = 'UPDATE ' . $this->schema . '.' . $this->table .' SET ';
      $parts = [];

      $param = array();
      foreach ($this->config->get('field') as $field) {
          if(in_array($field, array($this->getPrimarykey(), $this->table . "_modified", $this->table . "_created"))) {
              continue;
          }

          // If it exists, set the field
          if(array_key_exists($field, $data)) {

              if (is_object($data[$field]) || is_array($data[$field])) {
                  $data[$field] = $this->jsonEncode($data[$field]);
              }

              $var = $this->getStatementVariable($param, $field);

              // performance hack: store modelfield instance!
              if(!isset($this->modelfieldInstance[$field])) {
                $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
              }
              $fieldInstance = $this->modelfieldInstance[$field];

              $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
              $parts[] = $field . ' = ' . ':'.$var;
          }
      }
      $parts[] = $this->table . "_modified = now()";
      $query .= implode(',', $parts);

      // $params = array();
      $query .= $this->getFilterQuery($param);
      $this->doQuery($query, $param);

      return $this;
    }

    /**
     * [clearCache description]
     * @param  string $cacheGroup [description]
     * @param  string $cacheKey   [description]
     * @return void
     */
    protected function clearCache(string $cacheGroup, string $cacheKey) {
        $cacheObj = app::getCache();
        $cacheObj->clearKey($cacheGroup, $cacheKey);
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\modelInterface::delete($primaryKey)
     */
    public function delete($primaryKey = null) : \codename\core\model {
        if(!is_null($primaryKey)) {

            // TODO: remove/re-write
            if(strpos(get_class($this), 'activitystream') == false) {
                app::writeActivity("MODEL_DELETE", get_class($this), $primaryKey);
            }

            $this->deleteChildren($primaryKey);
            $query = "DELETE FROM " . $this->schema . "." . $this->table . " WHERE " . $this->getPrimarykey() . " = " . $primaryKey;
            $this->doQuery($query);
            return $this;
        }

        if(count($this->filter) == 0) {
            throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED', exception::$ERRORLEVEL_FATAL);
        }

        //
        // Old method: get all entries and delete them in separate queries
        //
        /* $entries = $this->addField($this->getPrimarykey())->search()->getResult();

        foreach($entries as $entry) {
            $this->delete($entry[$this->getPrimarykey()]);
        } */

        //
        // New method: use the filterquery to construct a single query delete statement
        //

        $query = "DELETE FROM " . $this->schema . "." . $this->table . ' ';

        // from search()
        // prepare an array for values to submit as PDO statement parameters
        // done by-ref, so the values are arriving right here after
        // running getFilterQuery()
        $params = array();

        $query .= $this->getFilterQuery($params);
        $this->doQuery($query, $params);

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::copy($primaryKey)
     */
    public function copy($primaryKey) : \codename\core\model {

    }

    /**
     *
     * @param string $operator
     * @return \codename\core\model\schematic\postgresql
     */
    public function setOperator(string $operator) : \codename\core\model {
        $this->filterOperator = $operator;
        return $this;
    }

    /**
     * returns a PDO variable name
     * that is kept safe from duplicates
     * using recursive calls to this function
     *
     * @param  array   $existingKeys [array of already existing variable names]
     * @param  string  $field        [the field base name]
     * @param  string  $add          [what is added to the base name]
     * @param  int     $c            [some extra factor (counter)]
     * @return string                [variable name]
     */
    protected function getStatementVariable(array $existingKeys, string $field, string $add = '', int $c = 0) {
      $name = str_replace('.', '_dot_', $field . (($add != '') ? ('_' . $add) : '') . (($c > 0) ? ('_' . $c) : ''));
      if(in_array($name, $existingKeys)) {
        return $this->getStatementVariable($existingKeys, $field, $add, ++$c);
      }
      return $name;
    }

    /**
     * [EXCEPTION_SQL_GETFILTERS_INVALID_QUERY description]
     * @var string
     */
    const EXCEPTION_SQL_GETFILTERS_INVALID_QUERY = 'EXCEPTION_SQL_GETFILTERS_INVALID_QUERY';

    /**
     * Converts the given array of model_plugin_filter instances to the WHERE... query string.
     * Is capable of using $flagfilters for binary operations
     * Handles named filtercollection groups
     * the respective filtercollection(s) (and their filters)
     *
     * returns a recursive array structure that can be converted to a query string
     *
     * @param array $filters            [array of filters]
     * @param array $flagfilters        [array of flagfilters]
     * @param array $filterCollections  [array of filter collections]
     * @param array &$appliedFilters    [cross-model-instance array of currently applied filters, to keep track of PDO variables]
     * @return array
     */
    public function getFilters(array $filters = array(), array $flagfilters = array(), array $filterCollections = array(), array &$appliedFilters = array()) : array {

        $where = [];

        // Loop through each filter
        foreach($filters as $filter) {

            // collect data for a single filter
            $filterQuery = [
              'conjunction' => $filter->conjunction ?? $this->filterOperator,
              'query' => null
            ];

            if($filter instanceof \codename\core\model\plugin\filter) {
              // handle regular filters

              if(is_array($filter->value)) {
                  // filter value is an array (e.g. IN() match)
                  $values = array();
                  $i = 0;
                  foreach($filter->value as $thisval) {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue(), $i++);
                      $values[] = ':' . $var; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field)); // values separated from query
                  }
                  $string = implode(', ', $values);
                  $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                  $filterQuery['query'] = $filter->field->getValue() . ' ' . $operator . ' ( ' . $string . ') ';
              } else {

                  // filter value is a singular value
                  // NOTE: $filter->value == 'null' (equality operator, compared to string) may evaluate to TRUE if you're passing in a positive boolean (!)
                  // instead, we're now using the identity operator === to explicitly check for a string 'null'
                  // NOTE: $filter->value == null (equality operator, compared to NULL) may evaluate to TRUE if you're passing in a negative boolean (!)
                  // instead, we're now using the identity operator === to explicitly check for a real NULL
                  // @see http://www.php.net/manual/en/types.comparisons.php
                  if(($filter->value === null) || (is_string($filter->value) && (strlen($filter->value) === 0)) || ($filter->value === 'null')) {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $filterQuery['query'] = $filter->field->getValue() . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' ' . ':'.$var . ' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                  } else {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $filterQuery['query'] = $filter->field->getValue() . ' ' . $filter->operator . ' ' . ':'.$var.' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field)); // values separated from query
                  }
              }
            } else if ($filter instanceof \codename\core\model\plugin\fieldfilter) {
              // handle field-based filters
              // this is not something PDO needs separately transmitted variables for
              // value IS indeed a field name
              $filterQuery['query'] = $filter->field->getValue() . ' = ' . $filter->value->getValue();
            }

            // only handle, if query set
            if($filterQuery['query'] != null) {
              $where[] = $filterQuery;
            } else {
              throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY, exception::$ERRORLEVEL_ERROR, $filter);
            }
        }

        // handle flag filters (bit-oriented)
        foreach($flagfilters as $flagfilter) {

          // collect data for a single filter
          $filterQuery = [
            'conjunction' => $filter->conjunction ?? $this->filterOperator,
            'query' => null
          ];

          $var = $this->getStatementVariable(array_keys($appliedFilters), $this->table.'_flag');

          if($flagfilter < 0) {
            $filterQuery['query'] = $this->table.'_flag & ' . ':'.$var . ' <> ' . ':'.$var . ' '; // var = PDO Param
            $appliedFilters[$var] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
          } else {
            $filterQuery['query'] = $this->table.'_flag & ' . ':'.$var . ' = ' . ':'.$var . ' '; // var = PDO Param
            $appliedFilters[$var] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
          }

          // we don't have to check for existance of 'query', as it is definitely handled
          // by the previous if-else clause
          $where[] = $filterQuery;
        }

        // collect groups of filter(collections)
        $t_filtergroups = array();

        // Loop through each named group
        foreach($filterCollections as $groupName => $groupFilterCollection) {

          // handle grouping of filtercollections
          // by default, there's only a single group ( e.g. 'default' )
          $t_groups = array();

          // Loop through each group member (which is a filtercollection) in a named group
          foreach($groupFilterCollection as $filterCollection) {

            // collect filters in a filtercollection
            $t_filters = array();

            // Loop through each filter in a filtercollection in a named group
            foreach($filterCollection['filters'] as $filter) {

              // collect data for a single filter
              $t_filter = [
                'conjunction' => $filterCollection['operator'],
                'query' => null
              ];

              if(is_array($filter->value)) {
                  // value is an array
                  $values = array();
                  $i = 0;
                  foreach($filter->value as $thisval) {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue(), $i++);
                      $values[] = ':' . $var; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field));
                  }
                  $string = implode(', ', $values);
                  $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                  $t_filter['query'] = $filter->field->getValue() . ' ' . $operator . ' ( ' . $string . ') ';
              } else {
                  // value is a singular value
                  // NOTE: see other $filter->value == null (equality or identity operator) note and others
                  if($filter->value === null || (is_string($filter->value) && strlen($filter->value) == 0) || $filter->value === 'null') {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $t_filter['query'] = $filter->field->getValue() . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' ' . ':'.$var . ' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                  } else {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $t_filter['query'] = $filter->field->getValue() . ' ' . $filter->operator . ' ' . ':'.$var.' ';
                      $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field));
                  }
              }

              if($t_filter['query'] != null) {
                $t_filters[] = $t_filter;
              } else {
                throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY, exception::$ERRORLEVEL_ERROR, $filter);
              }
            }

            if(count($t_filters) > 0) {
              // put all collected filters
              // into a recursive array structure
              $t_groups[] = [
                'conjunction' => $filterCollection['conjunction'] ?? $this->filterOperator,
                'query' => $t_filters
              ];
            }
          }

          // put all collected filtercollections in the named group
          // into a recursive array structure
          $t_filtergroups[] = [
            'conjunction' => $this->filterOperator,
            'query' => $t_groups
          ];
        }

        if(count($t_filtergroups) > 0) {
          // put all collected named groups
          // into a recursive array structure
          $where[] = [
            'conjunction' => $this->filterOperator,
            'query' => $t_filtergroups
          ];
        }

        // get filters from nested models recursively
        foreach($this->nestedModels as $join) {
          if($this->compatibleJoin($join->model)) {
            $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters));
          }
        }

        // get filters from sibling models recursively
        foreach($this->siblingModels as $join) {
          if($this->compatibleJoin($join->model)) {
            $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters));
          }
        }

        // return a recursive array structure
        // that contains all collected
        // - filters (filters, flagfilters, fieldfilters)
        // - named groups, containing
        // --- filtercollections, and their
        // ----- filters
        // everything with their conjunction parameter (AND/OR)
        // which is constructed on need in ::convertFilterQueryArray()
        return $where;
    }

    /**
     * [getFilterQuery description]
     * @param  array  &$appliedFilters [description]
     * @return string
     */
    public function getFilterQuery(array &$appliedFilters = array()) : string {
      $filterQueryArray = $this->getFilterQueryComponents($appliedFilters);
      if($this->saveLastFilterQueryComponents) {
        $this->lastFilterQueryComponents = $filterQueryArray;
      }
      if(count($filterQueryArray) > 0) {
        return ' WHERE ' . self::convertFilterQueryArray($filterQueryArray);
      } else {
        return '';
      }
    }

    /**
     * [protected description]
     * @var array
     */
    protected $lastFilterQueryComponents = null;

    /**
     * [protected description]
     * @var bool
     */
    protected $saveLastFilterQueryComponents = false;

    /**
     * [setSaveLastFilterQueryComponents description]
     * @param bool $state [description]
     */
    public function setSaveLastFilterQueryComponents(bool $state) {
      $this->saveLastFilterQueryComponents = $state;
    }

    /**
     * [getLastFilterQueryComponents description]
     * @return array|null
     */
    public function getLastFilterQueryComponents() {
      return $this->lastFilterQueryComponents;
    }

    /**
     * [getFilterQueryComponents description]
     * @param  array  &$appliedFilters [description]
     * @return array
     */
    public function getFilterQueryComponents(array &$appliedFilters = array()) : array {
      return $this->getFilters($this->filter, $this->flagfilter, $this->filterCollections, $appliedFilters);
    }

    /**
     * [convertFilterQueryArray description]
     * @param  array  $filterQueryArray [description]
     * @return string                   [description]
     */
    protected static function convertFilterQueryArray(array $filterQueryArray) : string {
      $queryPart = '';
      foreach($filterQueryArray as $index => $filterQuery) {
        if($index > 0) {
          $queryPart .= ' ' . $filterQuery['conjunction'] . ' ';
        }
        if(is_array($filterQuery['query'])) {
          $queryPart .= self::convertFilterQueryArray($filterQuery['query']);
        } else {
          $queryPart .= $filterQuery['query'];
        }
      }
      return '(' . $queryPart . ')';
    }

    /**
     * Converts the given array of model_plugin_order instances to the ORDER BY... query string
     * @param array $orders
     * @return string
     */
    protected function getOrders(array $orders) : string {
        // defaults
        $order = '';
        $appliedOrders = 0;

        // order fields
        foreach($orders as $myOrder) {
            $order .= ($appliedOrders > 0) ? ', ' : ' ORDER BY ';
            $identifier = array();

            if($myOrder->field->getSchema() != null) {
              $identifier[] = $myOrder->field->getSchema();
            }
            if($myOrder->field->getTable() != null) {
              $identifier[] = $myOrder->field->getTable();
            }
            if($myOrder->field->get() != null) {
              $identifier[] = $myOrder->field->get();
            }

            $order .= implode('.', $identifier) . ' ' . $myOrder->direction . ' ';
            $appliedOrders++;
        }

        return $order;
    }


    /**
     * Converts the given array of model_plugin_group instances to the GROUP BY... query string
     * @author Kevin Dargel
     * @param array $groups
     * @return string
     */
    protected function getGroups(array $groups) : string {
        // defaults
        $group = '';
        $appliedGroups = 0;
        // group by fields
        foreach($groups as $myGroup) {
            $group .= ($appliedGroups > 0) ? ', ' : ' GROUP BY ';
            $specifier = array();
            if($myGroup->field->getSchema() != null) {
              $specifier[] = $myGroup->field->getSchema();
            }
            if($myGroup->field->getTable() != null) {
              $specifier[] = $myGroup->field->getTable();
            }
            $specifier[] = $myGroup->field->get();
            $group .= implode('.', $specifier);
            $appliedGroups++;
        }
        return $group;
    }

    /**
     * Converts the given instance of model_plugin_limit to the LIMIT... query string
     * @param \codename\core\model\plugin\limit  $limit
     * @return string
     */
    protected function getLimit(\codename\core\model\plugin\limit $limit) : string {
        if ($limit->limit > 0) {
            return " LIMIT " . $limit->limit . " ";
        }
    }

    /**
     * Converts the given instance of model_plugin_offset to the OFFSET... query string
     * @param \codename\core\model\plugin\offset   $offset
     * @return string
     */
    protected function getOffset(\codename\core\model\plugin\offset $offset) : string {
        if ($offset->offset > 0) {
            return " OFFSET " . $offset->offset . " ";
        }
        return '';
    }

    /**
     * Converts the array of fields into the field list for the query "value1, value2 "
     * @param array $fields
     * @return string
     */
    protected function getFieldlist(array $fields) : string {
        $index = 0;
        $text = ' ';
        if(count($fields) > 0) {
            foreach($fields as $field) {
                if ($index > 0) {
                    $text .= ', ';
                }
                $text .= $field->field->get() . ' ';
                $index++;
            }
        }
        return $text;
    }

    /**
     * Returns the current fieldlist as an array of triples (schema, table, field)
     * it contains the visible fields of all nested models (childs, siblings)
     * retrieved in a recursive call
     * this also respects hiddenFields
     *
     * @author Kevin Dargel
     * @param string|null  $alias   [optional: alias as prefix for the following fields]
     * @return array
     */
    protected function getCurrentFieldlist(string $alias = null) : array {
      $result = array();
      if(count($this->fieldlist) == 0 && count($this->hiddenFields) > 0) {
        foreach($this->config->get('field') as $fieldName) {
          if(!in_array($fieldName, $this->hiddenFields)) {
            if($alias != null) {
              $result[] = array($alias, $fieldName);
            } else {
              $result[] = array($this->schema, $this->table, $fieldName);
            }
          }
        }
      } else {
        if(count($this->fieldlist) > 0) {
          foreach($this->fieldlist as $field) {
            if($field instanceof \codename\core\model\plugin\calculatedfield\calculatedfieldInterface) {
              $result[] = array($field->get());
            } else {
              if($alias != null) {
                $result[] = array($alias, $field->field->get());
              } else {
                $result[] = array($field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table, $field->field->get());
              }
            }
          }
        } else {
          if($alias != null) {
            $result[] = array($alias, '*');
          } else {
            $result[] = array($this->schema, $this->table, '*');
          }
        }
      }

      foreach($this->nestedModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $result = array_merge($result, $join->model->getCurrentFieldlist($join->currentAlias));
        }
      }
      foreach($this->siblingModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $result = array_merge($result, $join->model->getCurrentFieldlist($join->currentAlias));
        }
      }
      return $result;
    }

    /**
     * [protected description]
     * @var int|string|null|bool
     */
    protected $cachedLastInsertId = null;

    /**
     * returns the last inserted ID, if available
     * @return string [description]
     */
    public function lastInsertId () {
        return $this->cachedLastInsertId; // $this->db->lastInsertId();
    }

    /**
     * gets the current identifier of this model
     * in this case (sql), this is the table name
     * NOTE: schema is omitted here
     * @return string [table name]
     */
    public function getIdentifier() : string {
        return $this->table;
    }

    /**
     * {@inheritDoc}
     * @see \codename\core\model_interface::withFlag($flagval)
     */
    public function withFlag(int $flagval) : \codename\core\model {
        if(!in_array($flagval, $this->flagfilter)) {
          $this->flagfilter[] = $flagval;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutFlag(int $flagval): \codename\core\model
    {
      $flagval = $flagval * -1;
      if(!in_array($flagval, $this->flagfilter)) {
        $this->flagfilter[] = $flagval;
      }
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::withDefaultFlag($flagval)
     */
    public function withDefaultFlag(int $flagval) : \codename\core\model {
        if(!in_array($flagval, $this->defaultflagfilter)) {
          $this->defaultflagfilter[] = $flagval;
        }
        $this->flagfilter = array_merge($this->defaultflagfilter, $this->flagfilter);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutDefaultFlag(int $flagval): \codename\core\model
    {
      $flagval = $flagval * -1;
      if(!in_array($flagval, $this->defaultflagfilter)) {
        $this->defaultflagfilter[] = $flagval;
      }
      $this->flagfilter = array_merge($this->defaultflagfilter, $this->flagfilter);
      return $this;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(string $transactionName)
    {
      $this->db->beginVirtualTransaction($transactionName);
    }

    /**
     * @inheritDoc
     */
    public function endTransaction(string $transactionName)
    {
      $this->db->endVirtualTransaction($transactionName);
    }
}
