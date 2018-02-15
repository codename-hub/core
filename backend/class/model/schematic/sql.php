<?php
namespace codename\core\model\schematic;
use \codename\core\app;
use \codename\core\exception;
use \codename\core\model\plugin;

/**
 * base SQL specific SQL commands
 * @package core
 * @author Kevin Dargel
 * @since 2017-03-01
 */
abstract class sql extends \codename\core\model\schematic implements \codename\core\model\modelInterface {

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
     * Creates and configures the instance of the model. Fallback connection is 'default' database
     * @param string $connection Name of the connection in the app configuration file
     * @param string $schema Schema to use the model for
     * @param string $table Table to use the model on
     * @return model_schematic_postgresql
     */
    public function setConfig(string $connection = null, string $schema, string $table) : \codename\core\model {

        $this->schema = $schema;
        $this->table = $table;

        if($connection != null) {
        	$this->db = app::getDb($connection);
        }

        $config = app::getCache()->get('MODELCONFIG_', get_class($this));
        if(is_array($config)) {
            $this->config = new \codename\core\config($config);

            // Connection now defined in model .json
            if($this->config->exists("connection")) {
            	$connection = $this->config->get("connection");
            }
            $this->db = app::getDb($connection);

            return $this;
        }

        $this->config = $this->loadConfig();

        // Connection now defined in model .json
        if($this->config->exists("connection")) {
        	$connection = $this->config->get("connection");
        } else {
        	$connection = 'default';
        }

        $this->db = app::getDb($connection);

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
     * @return \codename\core\model
     */
    public function saveWithChildren(array $data) : \codename\core\model {

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
            $model = array_values($res)[0]->model;
            $model->saveWithChildren($data[$child]);
            // if we just inserted a NEW entry, get its primary key and save into the root model
            if(empty($data[$child][$model->getPrimaryKey()])) {
              $data2[$childConfig['field']] = $model->lastInsertId();
            }
          } else {
            // error?
            /* print_r($res);
            print_r($childConfig);
            print_r($this->getNestedJoins());
            */
            // die("BAP!");
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
      return $this->db->getResult();
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
     * @param  \codename\core\model $model      [model currently worked-on]
     * @param  array                $tableUsage [table usage as reference]
     * @return int                              [alias counter as reference]
     */
    public function deepJoin(\codename\core\model $model, array &$tableUsage = array(), int &$aliasCounter = 0) {
        if(count($model->getNestedJoins()) == 0 && count($model->getSiblingJoins()) == 0) {
            return '';
        }
        $ret = '';
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

            if(is_array($thisKey) || is_array($joinKey)) {
              // TODO: check for equal array item counts! otherwise: exception
              // perform a multi-component join
              foreach($thisKey as $index => $thisKeyValue) {
                $joinComponents[] = "{$alias}.{$joinKey[$index]} = {$this->table}.{$thisKeyValue}";
              }
            } else {
              $joinComponents[] = "{$alias}.{$joinKey} = {$this->table}.{$thisKey}";
            }

            // add conditions!
            foreach($join->conditions as $filter) {
              $operator = $filter['value'] == null ? ($filter['operator'] == '!=' ? 'IS NOT' : 'IS') : $filter['operator'];
              $value = $filter['value'] == null ? 'NULL' : $filter['value'];
              $joinComponents[] = "{$this->table}.{$filter['field']} {$operator} {$value}";
            }

            $joinComponentsString = implode(' AND ', $joinComponents);
            $ret .= " {$joinMethod} {$nest->schema}.{$nest->table} {$aliasAs} ON $joinComponentsString";

            $join->currentAlias = $alias;

            $ret .= $nest->deepJoin($nest, $tableUsage, $aliasCounter);
        }
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
        $query = "SELECT ";

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

        if(count($this->order) > 0) {
            $query .= $this->getOrders($this->order);
        }

        if(count($this->group) > 0) {
            $query .= $this->getGroups($this->group);
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
                  $this->modelfieldInstance[$field] = new \codename\core\value\text\modelfield($field);
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

    protected function saveCreate(array $data, array &$param = array()) {
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
                  $this->modelfieldInstance[$field] = new \codename\core\value\text\modelfield($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));

                $query .= ':'.$var;
            }
        }
        $query .= " );";
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
     * @param array [or even an object?]
     * @return string [json-encoded string]
     */
    protected function jsonEncode($data) : string {
      return json_encode($data);
    }

    protected function dataImporta(array $data) : array {

    }

    protected function saveLog(string $mode, array $data) {
        if(strpos(get_class($this), 'activitystream') == false) {
            app::writeActivity("MODEL_" . $mode, get_class($this), $data);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::save($data)
     */
    public function save(array $data) : \codename\core\model {
        $params = array();
        if (array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0) {
            $query = $this->saveUpdate($data, $params);
            $this->doQuery($query, $params);
        } else {
            $query = $this->saveCreate($data, $params);
            $this->doQuery($query, $params);
        }
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function calcField(string $fieldname, string $parse) : \codename\core\model {
        $class = "\codename\core\model_plugin_field_" . $this->getType();
        array_push($this->fieldlist, new $class($parse . ' AS ' . $fieldname));
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    protected function clearCache(string $cacheGroup, string $cacheKey) {
        $cacheObj = app::getCache();
        $cacheObj->clearKey($cacheGroup, $cacheKey);
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::delete($primaryKey)
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
            return $this;
        }

        $entries = $this->addField($this->getPrimarykey())->search()->getResult();

        foreach($entries as $entry) {
            $this->delete($entry[$this->getPrimarykey()]);
        }

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

    protected function getStatementVariable(array $existingKeys, string $field, string $add = '', int $c = 0) {
      $name = str_replace('.', '_dot_', $field . (($add != '') ? ('_' . $add) : '') . (($c > 0) ? ('_' . $c) : ''));
      if(in_array($name, $existingKeys)) {
        return $this->getStatementVariable($existingKeys, $field, $add, ++$c);
      }
      return $name;
    }

    /**
     * Converts the given array of model_plugin_filter instances to the WHERE... query string. Is capable of using $flagfilters for binary operations
     * @param array $filters
     * @param array $flagfilters
     * @return string
     */
    public function getFilters(array $filters = array(), array $flagfilters = array(), array $filterCollections = array(), array &$appliedFilters = array()) : string {

        $where = '';
        foreach($filters as $filter) {
            $where .= (count($appliedFilters) > 0) ? ' ' . $this->filterOperator . ' ' : ' WHERE ';

            if($filter instanceof \codename\core\model\plugin\filter) {
              // handle regular filters
              if(is_array($filter->value)) {
                  $values = array();
                  $i = 0;
                  foreach($filter->value as $thisval) {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue(), $i++);
                      $values[] = ':' . $var; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field)); // values separated from query
                  }
                  $string = implode(', ', $values);
                  $where .= $filter->field->getValue() . ' IN ( ' . $string . ') ';
              } else {
                  if(is_null($filter->value) || (is_string($filter->value) && strlen($filter->value) == 0) || $filter->value == 'null') {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $where .= $filter->field->getValue() . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' ' . ':'.$var . ' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                  } else {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $where .= $filter->field->getValue() . ' ' . $filter->operator . ' ' . ':'.$var.' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field)); // values separated from query
                  }
              }
            } else if ($filter instanceof \codename\core\model\plugin\fieldfilter) {
              // handle field-based filters
              $where .= $filter->field->getValue() . ' = ' . $filter->value->getValue();
              $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue().'==='.$filter->field->getValue());
              $appliedFilters[$var] = null; // $this->getParametrizedValue($filter->value->getValue(), $this->getFieldtype($filter->field)); // values separated from query
            }
        }

        foreach($flagfilters as $flagfilter) {
            $where .= (count($appliedFilters) > 0) ? ' ' . $this->filterOperator . ' ' : ' WHERE ';
            $var = $this->getStatementVariable(array_keys($appliedFilters), $this->table.'_flag');
            if($flagfilter < 0) {
              $where .= $this->table.'_flag & ' . ':'.$var . ' <> ' . ':'.$var . ' '; // var = PDO Param
              $appliedFilters[$var] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
            } else {
              $where .= $this->table.'_flag & ' . ':'.$var . ' = ' . ':'.$var . ' '; // var = PDO Param
              $appliedFilters[$var] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
            }
        }

        $t_filtergroups = array();
        $t_appliedfiltergroups = 0;

        // Count of applied filters before going into filter collections
        $appliedFilterCountBefore = count($appliedFilters);

        foreach($filterCollections as $filterCollection) {
          $t_appliedFilters = 0; // contains key => value for pdo prepStmt
          $t_filters = array();
          foreach($filterCollection['filters'] as $filter) {
            $t_filters[] = ($t_appliedFilters > 0) ? ' ' . $filterCollection['operator'] . ' ' : '';
            if(is_array($filter->value)) {
                $values = array();
                $i = 0;
                foreach($filter->value as $thisval) {
                    $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue(), $i++);
                    $values[] = ':' . $var; // var = PDO Param
                    $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field));
                }
                $string = implode(', ', $values);
                $t_filters[] = $filter->field->getValue() . ' IN ( ' . $string . ') ';
            } else {
                if(is_null($filter->value) || (is_string($filter->value) && strlen($filter->value) == 0) || $filter->value == 'null') {
                    $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                    $t_filters[] = $filter->field->getValue() . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' ' . ':'.$var . ' '; // var = PDO Param
                    $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                } else {
                    $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                    $t_filters[] = $filter->field->getValue() . ' ' . $filter->operator . ' ' . ':'.$var.' ';
                    $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field));
                }
            }
            $t_appliedFilters++;
          }

          if(sizeof($t_filters) > 0) {
            $t_filtergroups[] = ($t_appliedfiltergroups>0 ? ' ' . $this->filterOperator . ' ' : '') .  ' ( ' . implode('', $t_filters) . ' ) ';
            $t_appliedfiltergroups++;
          }
        }

        if(sizeof($t_filtergroups) > 0) {
          $where .= ($appliedFilterCountBefore > 0) ? ' ' . $this->filterOperator . ' ' : ' WHERE ';
          $where .= '(';
          foreach($t_filtergroups as $filtergroup) {
            $where .= '' . $filtergroup;
          }
          $where .= ')';
        }

        foreach($this->nestedModels as $join) {
          if($this->compatibleJoin($join->model)) {
            $where .= $join->model->getFilterQuery($appliedFilters);
          }
        }
        foreach($this->siblingModels as $join) {
          if($this->compatibleJoin($join->model)) {
            $where .= $join->model->getFilterQuery($appliedFilters);
          }
        }

        return $where;
    }

    public function getFilterQuery(array &$appliedFilters = array()) : string {
      return $this->getFilters($this->filter, $this->flagfilter, $this->filterCollections, $appliedFilters);
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
     * @param model_plugin_limit $limit
     * @return string
     */
    protected function getLimit(\codename\core\model\plugin\limit $limit) : string {
        if ($limit->limit > 0) {
            return " LIMIT " . $limit->limit . " ";
        }
    }

    /**
     * Converts the given instance of model_plugin_offset to the OFFSET... query string
     * @param model_plugin_offset $offset
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
     * @author Kevin Dargel
     * @return array[]
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
     * @todo bring me to life!
     */
    public function lastInsertId () : string {
        return $this->db->lastInsertId();
    }

    /**
     * @todo DOCUMENTATION
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

}
