<?php

namespace codename\core\model\schematic;

use codename\core\app;
use codename\core\config;
use codename\core\database;
use codename\core\exception;
use codename\core\model;
use codename\core\model\discreteModelSchematicSqlInterface;
use codename\core\model\modelInterface;
use codename\core\model\plugin\aggregate\aggregateInterface;
use codename\core\model\plugin\calculatedfield\calculatedfieldInterface;
use codename\core\model\plugin\fieldfilter;
use codename\core\model\plugin\filter;
use codename\core\model\plugin\filter\filterInterface;
use codename\core\model\plugin\filterlist\filterlistInterface;
use codename\core\model\plugin\fulltext\fulltextInterface;
use codename\core\model\plugin\join;
use codename\core\model\plugin\join\recursive;
use codename\core\model\plugin\limit;
use codename\core\model\plugin\managedFilterInterface;
use codename\core\model\plugin\offset;
use codename\core\model\plugin\sqlCteStatementInterface;
use codename\core\model\schematic;
use codename\core\model\schemeless\json;
use codename\core\model\timemachineInterface;
use codename\core\model\virtualFieldResultInterface;
use codename\core\timemachine;
use codename\core\transaction\transactionableInterface;
use codename\core\value\text\modelfield;
use LogicException;
use PDO;
use ReflectionException;

use function array_keys;
use function count;
use function implode;
use function in_array;
use function is_array;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strlen;

/**
 * base SQL specific SQL commands
 * @package core
 * @since 2017-03-01
 */
abstract class sql extends schematic implements modelInterface, virtualFieldResultInterface, transactionableInterface
{
    /**
     * invalid foreign key config during deepJoin()
     * @var string
     */
    public const EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG = "EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG";
    /**
     * Exception thrown when a model is missing a field that is required by the framework
     * (e.g. _created and/or _modified)
     * @var string
     */
    public const EXCEPTION_MODEL_CONFIG_MISSING_FIELD = 'EXCEPTION_MODEL_CONFIG_MISSING_FIELD';
    /**
     * [EXCEPTION_SQL_GETFILTERS_INVALID_QUERY description]
     * @var string
     */
    public const EXCEPTION_SQL_GETFILTERS_INVALID_QUERY = 'EXCEPTION_SQL_GETFILTERS_INVALID_QUERY';
    /**
     * [EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE description]
     * @var string
     */
    public const EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE = 'EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE';
    /**
     * Use right joining for this model
     * which allows empty joined fields to appear
     * @var bool
     */
    public bool $rightJoin = false;
    /**
     * The search method will use this as the filter operator
     * @var string $filterOperator
     */
    protected string $filterOperator = ' AND ';
    /**
     * config option that configures database connection (PDO) storage factor
     * @var bool
     */
    protected bool $storeConnection = true;
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
    protected bool $virtualFieldResult = false;
    /**
     * [protected description]
     * @var bool|null
     */
    protected ?bool $useTimemachineState = null;
    /**
     * Whether to set *_modified field automatically
     * during update
     * @var bool
     */
    protected bool $saveUpdateSetModifiedTimestamp = true;

    /**
     * [protected description]
     * @var modelfield[]
     */
    protected array $modelfieldInstance = [];
    /**
     * [protected description]
     * @var null|array
     */
    protected ?array $lastFilterQueryComponents = null;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $saveLastFilterQueryComponents = false;
    /**
     * [protected description]
     * @var int|string|null|bool
     */
    protected int|string|bool|null $cachedLastInsertId = null;
    /**
     * [private description]
     * @var bool
     */
    private bool $countingModeOverride = false;

    /**
     * Creates and configures the instance of the model. Fallback connection is 'default' database
     * @param string|null $connection [Name of the connection in the app configuration file]
     * @param string $schema [Schema to use the model for]
     * @param string $table [Table to use the model on]
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function setConfig(?string $connection, string $schema, string $table): model
    {
        $this->schema = $schema;
        $this->table = $table;

        if ($connection != null) {
            $this->db = app::getDb($connection, $this->storeConnection);
        }

        $config = app::getCache()->get('MODELCONFIG_', $this->getModelconfigCacheKey());
        if (is_array($config)) {
            $this->config = new config($config);

            // Connection now defined in model .json
            if ($this->config->exists("connection")) {
                $connection = $this->config->get("connection");
            }
            $this->db = app::getDb($connection, $this->storeConnection);

            return $this;
        }

        if (!$this->config) {
            $this->config = $this->loadConfig();
        }

        // Connection now defined in model .json
        if ($this->config->exists("connection")) {
            $connection = $this->config->get("connection");
        } else {
            $connection = 'default';
        }

        if (!$this->db) {
            $this->db = app::getDb($connection, $this->storeConnection);
        }

        if (!in_array("{$this->table}_created", $this->config->get("field"))) {
            throw new exception('EXCEPTION_MODEL_CONFIG_MISSING_FIELD', exception::$ERRORLEVEL_FATAL, "{$this->table}_created");
        }
        if (!in_array("{$this->table}_modified", $this->config->get("field"))) {
            throw new exception('EXCEPTION_MODEL_CONFIG_MISSING_FIELD', exception::$ERRORLEVEL_FATAL, "{$this->table}_modified");
        }

        app::getCache()->set('MODELCONFIG_', $this->getModelconfigCacheKey(), $this->config->get());
        return $this;
    }

    /**
     * returns the cache key to be used for the config
     * @return string
     * @throws exception
     */
    protected function getModelconfigCacheKey(): string
    {
        if ($this->schema && $this->table) {
            return get_class($this) . '-' . $this->schema . '_' . $this->table;
        } else {
            throw new exception('EXCEPTION_MODELCONFIG_CACHE_KEY_MISSING_DATA', exception::$ERRORLEVEL_FATAL);
        }
    }

    /**
     * loads a new config file (uncached)
     * @return config
     * @throws ReflectionException
     * @throws exception
     */
    protected function loadConfig(): config
    {
        return new config\json('config/model/' . $this->schema . '_' . $this->table . '.json', true, true);
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
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function saveWithChildren(array $data): model
    {
        // Open a virtual transaction
        // as we might do some multimodel saving
        $this->db->beginVirtualTransaction();

        $data2 = $data;

        //
        // delay collection saving
        // we might need the pkey, if the base model entry is not yet created
        //
        $childCollectionSaves = [];

        // save children
        if ($this->config->exists('children')) {
            foreach ($this->config->get('children') as $child => $childConfig) {
                if ($childConfig['type'] === 'foreign') {
                    //
                    // Foreign Key based child saving
                    //

                    // get the nested models / join plugin instances
                    $foreignConfig = $this->config->get('foreign>' . $childConfig['field']);
                    $field = $childConfig['field'];

                    // get the join plugin valid for the child reference field
                    $res = array_filter($this->getNestedJoins(), function (join $join) use ($field) {
                        return $join->modelField == $field;
                    });

                    if (count($res) === 1) {
                        // NOTE: array_filter preserves keys. use array_values to simply use index 0
                        // TODO: check for required fields...
                        if (isset($data[$child])) {
                            $model = array_values($res)[0]->model;
                            $model->saveWithChildren($data[$child]);
                            // if we just inserted a NEW entry, get its primary key and save into the root model
                            if (empty($data[$child][$model->getPrimaryKey()])) {
                                $data2[$childConfig['field']] = $model->lastInsertId();
                            } else {
                                $data2[$childConfig['field']] = $data[$child][$model->getPrimaryKey()];
                            }
                        }
                    } elseif (count($res) > 1) {
                        throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_CHILDREN_AMBIGUOUS_JOINS', exception::$ERRORLEVEL_ERROR, [
                          'child' => $child,
                          'childConfig' => $childConfig,
                          'foreign' => $field,
                          'foreignConfig' => $foreignConfig,
                        ]);
                    }
                    // TODO: make sure we should do it like that.
                    unset($data2[$child]);
                } elseif ($childConfig['type'] === 'collection') {
                    //
                    // Collection Saving of children
                    //

                    // collection saving done below
                    if (isset($this->collectionPlugins[$child]) && array_key_exists($child, $data)) {
                        $childCollectionSaves[$child] = $data[$child];
                    }

                    // unset the child collection field
                    // as it cannot be handled by SQL
                    unset($data2[$child]);
                }
            }
        }
        // end save children

        //
        // Save the main dataset
        //
        $this->save($data2);

        //
        // Determine, if we're updating OR inserting (depending on PKEY value existence)
        //
        $update = (array_key_exists($this->getPrimaryKey(), $data) && strlen($data[$this->getPrimaryKey()]) > 0);
        if (!$update) {
            $data[$this->getPrimaryKey()] = $this->lastInsertId();
        }

        //
        // Save child collections
        //
        if (count($childCollectionSaves) > 0) {
            foreach ($childCollectionSaves as $child => $childData) {
                if ($childData === null) {
                    continue;
                }

                $collection = $this->collectionPlugins[$child];
                $model = $collection->collectionModel;

                // TODO: get all existing references/entries
                // that must be deleted/obsoleted
                $model->addFilter($collection->getCollectionModelBaseRefField(), $data[$collection->getBaseField()]);
                $existingCollectionItems = $model->search()->getResult();

                // determine must-have-pkeys
                $targetStateIds = array_reduce($childData, function ($carry, $item) use ($model) {
                    if ($id = ($item[$model->getPrimaryKey()] ?? null)) {
                        $carry[] = $id;
                    }
                    return $carry;
                }, []);

                // determine must-have-pkeys
                $existingIds = array_reduce($existingCollectionItems, function ($carry, $item) use ($model) {
                    if ($id = ($item[$model->getPrimaryKey()] ?? null)) {
                        $carry[] = $id;
                    }
                    return $carry;
                }, []);

                // the difference - to-be-deleted IDs
                $deleteIds = array_diff($existingIds, $targetStateIds);

                // delete them
                foreach ($deleteIds as $id) {
                    $model->delete($id);
                }

                foreach ($childData as $childValue) {
                    // TODO?: check for references!
                    // For now, we're just overwriting the reference to THIS model / current dataset
                    if (!isset($childValue[$collection->getCollectionModelBaseRefField()]) || ($childValue[$collection->getCollectionModelBaseRefField()] != $data[$collection->getBaseField()])) {
                        $childValue[$collection->getCollectionModelBaseRefField()] = $data[$collection->getBaseField()];
                    }
                    $model->saveWithChildren($childValue);
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
     * returns the last inserted ID, if available
     * @return int|string|bool|null [description]
     */
    public function lastInsertId(): int|string|bool|null
    {
        return $this->cachedLastInsertId;
    }

    /**
     *
     * {@inheritDoc}
     * @param array $data [description]
     * @return model         [description]
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\modelInterface::save($data)
     *
     * [save description]
     */
    public function save(array $data): model
    {
        $params = [];
        if (array_key_exists($this->getPrimaryKey(), $data) && strlen($data[$this->getPrimaryKey()]) > 0) {
            $query = $this->saveUpdate($data, $params);
            $this->doQuery($query, $params);
            if ($this->db->affectedRows() !== 1) {
                throw new exception('MODEL_SAVE_UPDATE_FAILED', exception::$ERRORLEVEL_ERROR);
            }
        } else {
            $query = $this->saveCreate($data, $params);
            $this->cachedLastInsertId = null;
            $this->doQuery($query, $params);
            $this->cachedLastInsertId = $this->db->lastInsertId();

            //
            // affected rows might be != 1 (e.g. 2 on MySQL)
            // of doing a saveCreate with replace = true
            // (in overridden classes)
            // This WILL fail at this point.
            //
            if ($this->db->affectedRows() !== 1) {
                throw new exception('MODEL_SAVE_CREATE_FAILED', exception::$ERRORLEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * returns a query that performs a save using UPDATE
     * (e.g. we have an existing entry that needs to be updated)
     * @param array $data [data]
     * @param array  &$param [reference array that keeps track of PDO variable names]
     * @return string         [query]
     * @throws ReflectionException
     * @throws exception
     */
    protected function saveUpdate(array $data, array &$param = []): string
    {
        //
        // disable cache reset, if model is not enabled for it.
        // At the moment, we don't even use the PRIMARY cache
        //
        if ($this->cache) {
            $cacheGroup = $this->getCacheGroup();
            $cacheKey = "PRIMARY_" . $data[$this->getPrimaryKey()];
            $this->clearCache($cacheGroup, $cacheKey);
        }

        $raw = [];
        // raw data for usage with the timemachine
        if ($this->useTimemachine()) {
            $raw = $data;
        }

        $query = 'UPDATE ' . $this->getTableIdentifier() . ' SET ';
        $parts = [];

        foreach ($this->config->get('field') as $field) {
            if (in_array($field, [$this->getPrimaryKey(), $this->table . "_modified", $this->table . "_created"])) {
                continue;
            }

            // If it exists, set the field
            if (array_key_exists($field, $data)) {
                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }

                $var = $this->getStatementVariable(array_keys($param), $field);

                // performance hack: store modelfield instance!
                if (!isset($this->modelfieldInstance[$field])) {
                    $this->modelfieldInstance[$field] = modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                $parts[] = $field . ' = ' . ':' . $var;
            }
        }

        if ($this->saveUpdateSetModifiedTimestamp) {
            $parts[] = $this->table . "_modified = " . $this->getServicingSqlInstance()->getSaveUpdateSetModifiedTimestampStatement($this);
        }
        $query .= implode(',', $parts);

        $var = $this->getStatementVariable(array_keys($param), $this->getPrimaryKey());
        // use timemachine, if capable and enabled
        // this stores delta values in a separate model

        if ($this->useTimemachine()) {
            $tm = timemachine::getInstance($this->getIdentifier());
            $tm->saveState($data[$this->getPrimaryKey()], $raw); // we have to use raw data, as we can't use jsonfield arrays.
        }

        $param[$var] = $this->getParametrizedValue($data[$this->getPrimaryKey()], 'number_natural'); // ? hardcoded type?

        $query .= " WHERE " . $this->getPrimaryKey() . " = " . ':' . $var;
        return $query;
    }

    /**
     * [clearCache description]
     * @param string $cacheGroup [description]
     * @param string $cacheKey [description]
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function clearCache(string $cacheGroup, string $cacheKey): void
    {
        $cacheObj = app::getCache();
        $cacheObj->clearKey($cacheGroup, $cacheKey);
    }

    /**
     * Whether this model is timemachine-capable and enabled
     * @return bool
     */
    protected function useTimemachine(): bool
    {
        if ($this->useTimemachineState === null) {
            $this->useTimemachineState = ($this instanceof timemachineInterface) && $this->isTimemachineEnabled();
        }
        return $this->useTimemachineState;
    }

    /**
     * Returns a db-specific identifier (e.g. schema.table for the current model)
     * or, if schema and model are specified, for a different schema+table
     * @param string|null $schema [name of schema]
     * @param string|null $model [name of schema]
     * @return string         [description]
     * @throws exception
     */
    public function getTableIdentifier(?string $schema = null, ?string $model = null): string
    {
        if ($schema || $model) {
            return $this->getServicingSqlInstance()->getTableIdentifierParametrized($schema, $model);
        } else {
            return $this->getServicingSqlInstance()->getTableIdentifier($this);
        }
    }

    /**
     * [getServicingSqlInstance description]
     * @return model\servicing\sql [description]
     * @throws exception
     */
    protected function getServicingSqlInstance(): model\servicing\sql
    {
        if ($this->servicingInstance === null) {
            $this->initServicingInstance();
        }
        return $this->servicingInstance;
    }

    /**
     * {@inheritDoc}
     * @throws exception
     */
    protected function initServicingInstance(): void
    {
        try {
            $class = app::getInheritedClass('model_servicing_sql_' . $this->getType());
            $this->servicingInstance = new $class();
            return;
        } catch (\Exception) {
        }

        if ($this->servicingInstance === null) {
            throw new exception('EXCEPTION_MODEL_FAILED_INIT_SERVICING_INSTANCE', exception::$ERRORLEVEL_FATAL);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(): string
    {
        return $this->db->driver;
    }

    /**
     * json_encode wrapper
     * for customizing the output sent to the database
     * Reason: pgsql is handling the encoding for itself
     * but MySQL is doing strict encoding handling
     * @see http://stackoverflow.com/questions/4782319/php-json-encode-utf8-char-problem-mysql
     * and esp. @see http://stackoverflow.com/questions/4782319/php-json-encode-utf8-char-problem-mysql/37353316#37353316
     *
     * @param object|array $data [or even an object?]
     * @return string   [json-encoded string]
     * @throws exception
     */
    protected function jsonEncode(object|array $data): string
    {
        return $this->getServicingSqlInstance()->jsonEncode($data);
    }

    /**
     * returns a PDO variable name
     * that is kept safe from duplicates
     * using recursive calls to this function
     *
     * @param array $existingKeys [array of already existing variable names]
     * @param string $field [the field base name]
     * @param string $add [what is added to the base name]
     * @param int $c [some extra factor (counter)]
     * @return string                [variable name]
     */
    protected function getStatementVariable(array $existingKeys, string $field, string $add = '', int $c = 0): string
    {
        if ($c === 0) {
            $baseName = str_replace('.', '_dot_', $field . (($add != '') ? ('_' . $add) : ''));
            $baseName = preg_replace('/[^\w]+/', '_', $baseName);
        } else {
            $baseName = $field;
        }
        $name = $baseName . (($c > 0) ? ('_' . $c) : '');
        if (in_array($name, $existingKeys)) {
            return $this->getStatementVariable($existingKeys, $baseName, $add, ++$c);
        }
        return $name;
    }

    /**
     * get a parametrized value (array)
     * for use with PDO
     * @param mixed $value [description]
     * @param string $fieldtype [description]
     * @return array             [description]
     */
    protected function getParametrizedValue(mixed $value, string $fieldtype): array
    {
        if ($value === null) {
            $param = PDO::PARAM_NULL; // Explicit NULL
        } elseif ($fieldtype == 'number') {
            $value = (float)$value;
            $param = PDO::PARAM_STR; // explicitly use this one...
        } elseif (($fieldtype === 'number_natural') || is_int($value)) {
            // NOTE: if integer value supplied, explicitly use this as param type
            $param = PDO::PARAM_INT;
        } elseif ($fieldtype == 'boolean') {
            //
            // Temporary workaround for MySQL being so odd.
            // bool == tinyint(1) in MySQL-world. So, we pre-evaluate
            // the value to 0 or 1 (NULL being handled above)
            //
            $value = $value ? 1 : 0;
            $param = PDO::PARAM_INT;
        // $param = \PDO::PARAM_BOOL;
        } else {
            $param = PDO::PARAM_STR; // Fallback
        }
        return [
          $value,
          $param,
        ];
    }

    /**
     * gets the current identifier of this model
     * in this case (sql), this is the table name
     * NOTE: schema is omitted here
     * @return string [table name]
     */
    public function getIdentifier(): string
    {
        return $this->table;
    }

    /**
     * returns a query that performs a save using INSERT
     * @param array $data [data]
     * @param array  &$param [reference array that keeps track of PDO variable names]
     * @param bool $replace [use replace on duplicate unique/pkey]
     * @return string           [query]
     * @throws ReflectionException
     * @throws exception
     */
    protected function saveCreate(array $data, array &$param = [], bool $replace = false): string
    {
        // TEMPORARY: SAVE LOG DISABLED
        // $this->saveLog('CREATE', $data);

        $query = 'INSERT INTO ' . $this->getTableIdentifier() . ' ';
        $query .= ' (';
        $index = 0;
        foreach ($this->config->get('field') as $field) {
            if ($field == $this->getPrimaryKey() || in_array($field, [$this->table . "_modified", $this->table . "_created"])) {
                continue;
            }
            if (array_key_exists($field, $data)) {
                if ($index > 0) {
                    $query .= ', ';
                }
                $index++;
                $query .= $field;
            }
        }
        $query .= ') VALUES (';
        $index = 0;
        foreach ($this->config->get('field') as $field) {
            if ($field == $this->getPrimaryKey() || in_array($field, [$this->table . "_modified", $this->table . "_created"])) {
                continue;
            }
            if (array_key_exists($field, $data)) {
                if ($index > 0) {
                    $query .= ', ';
                }

                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }
                $index++;

                $var = $this->getStatementVariable(array_keys($param), $field);

                // performance hack: store modelfield instance!
                if (!isset($this->modelfieldInstance[$field])) {
                    $this->modelfieldInstance[$field] = modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));

                $query .= ':' . $var;
            }
        }
        $query .= " )";
        if ($replace) {
            $query .= ' ON DUPLICATE KEY UPDATE ';
            $parts = [];
            foreach ($this->config->get('field') as $field) {
                if ($field == $this->getPrimaryKey() || in_array($field, [$this->table . "_modified", $this->table . "_created"])) {
                    continue;
                }
                if (array_key_exists($field, $data)) {
                    $parts[] = "$field = VALUES($field)";
                }
            }
            $query .= implode(',', $parts);
        }
        $query .= ";";
        return $query;
    }

    /**
     *
     * {@inheritDoc}
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\model_interface::search()
     */
    public function search(): model
    {
        if ($this->filterDuplicates) {
            $query = "SELECT DISTINCT ";
        } else {
            $query = "SELECT ";
        }

        // first: deepJoin to get correct alias names
        //
        // Contains Fix for JIRA [CODENAME-493]
        // see below. We include the main table here, from the start.
        // As it simply IS part of the used tables.
        //
        $tableUsage = ["$this->schema.$this->table" => 1];

        // prepare an array for values to submit as PDO statement parameters
        // done by-ref, so the values are arriving right here after
        // running getFilterQuery()
        $params = [];

        // ADDED 2021-05-05: CTEs
        $cte = [];

        $cteName = null;
        if ($this->recursive) {
            $cteName = '__cte_recursive_1';
            $cte[] = $this->getRecursiveSqlCteStatement($cteName, $params);
            $tableUsage[$cteName] = 1;
        }

        $explicitDiscrete = false;

        // Root model is a discrete model
        // Use getTableIdentifier for setting a main alias
        if ($this->isDiscreteModel()) {
            $cteName = $this->getTableIdentifier();
            $tableUsage[$cteName] = 1;
            $explicitDiscrete = true;
        }

        //
        // NOTE/CHANGED 2020-09-15: allow params in deepJoin() (conditions!)
        //
        $aliasCounter = 0;
        $parentAlias = null;
        $deepjoin = $this->deepJoin($this, $tableUsage, $aliasCounter, $parentAlias, $params, $cte);

        // Prepend CTEs, if there are any
        // We default to WITH RECURSIVE as we do not track whether they are or not.
        // This leads to the fact
        // - we do not have to take care of the order of the CTEs
        // - we simply enable RECURSIVE by default, no matter we really use it
        if (count($cte) > 0) {
            $query = 'WITH RECURSIVE ' . implode(", \n", $cte) . "\n" . $query;
        }

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if ($this->countingModeOverride) {
            $query .= 'COUNT(' . $this->getTableIdentifier() . '.' . $this->wrapIdentifier($this->getPrimaryKey()) . ') as ___count';
        } else {
            // retrieve a list of all model field lists, recursively
            // respecting hidden fields and duplicate field names in other models/tables
            $fieldlist = $this->getCurrentFieldlist($cteName, $params);

            if (count($fieldlist) == 0) {
                $query .= ' * ';
            } else {
                $fields = [];
                foreach ($fieldlist as $f) {
                    // schema and table specifier separator (.)(.)
                    // schema.table.field (and field may be a '*')
                    $fields[] = implode('.', $f);
                }
                // chain the fields
                $query .= implode(',', $fields);
            }
        }

        //
        // CHANGED/ADDED 2020-06-10 Discrete models (empowering sub queries)
        // NOTE: we're checking for discrete models here
        // as they don't represent a table on its own, but merely an entire subquery
        //
        if ($cteName !== null && !$explicitDiscrete) {
            $query .= ' FROM ' . $cteName . ' ';
        } elseif ($this->isDiscreteModel() && $this instanceof discreteModelSchematicSqlInterface) {
            $query .= ' FROM ' . $this->getDiscreteModelQuery($params) . ' AS ' . $this->table . ' '; // directly apply table alias
        } else {
            $query .= ' FROM ' . $this->getTableIdentifier() . ' ';
        }

        if (($this->useIndex ?? false) && count($this->useIndex) > 0) {
            $query .= 'USE INDEX(' . $this->useIndex[0] . ') ';
        }

        // append the previously constructed deepjoin string
        $query .= $deepjoin;

        //
        // Fix for JIRA [CODENAME-493]
        // Provide current main table specifier
        // as pseudo-alias
        // to fix multiple usage of the same model

        // CHANGED 2020-11-26: set root table name, by default (mainAlias)
        // To ensure correct duplicate field name handling across multiple tables
        // $mainAlias = "{$this->schema}.{$this->table}";
        // CHANGED again: we HAVE to omit setting the mainAlias by default
        // As this crashes queries using pre-set schema names
        $mainAlias = null;
        if ($tableUsage["$this->schema.$this->table"] > 1) {
            $mainAlias = $this->getTableIdentifier();
        }

        $query .= $this->getFilterQuery($params, $mainAlias);

        $groups = $this->getGroups($mainAlias);
        if (count($groups) > 0) {
            $query .= ' GROUP BY ' . implode(', ', $groups);
        }

        //
        // HAVING clause
        //
        // $appliedAggregateFilters = [];
        $aggregate = $this->getAggregateQueryComponents($params);
        if (count($aggregate) > 0) {
            $query .= ' HAVING ' . self::convertFilterQueryArray($aggregate);
        }

        if (count($this->order) > 0) {
            $query .= $this->getOrders($this->order);
        }

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if (!$this->countingModeOverride) {
            if (!is_null($this->limit)) {
                $query .= $this->getLimit($this->limit);
            }

            if (!is_null($this->offset) > 0) {
                $query .= $this->getOffset($this->offset);
            }
        }

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if ($this->countingModeOverride && count($groups) >= 1) {
            $query = 'SELECT COUNT(___count) AS ___count FROM(' . $query . ') AS DerivedTableAlias';
        }

        $this->doQuery($query, $params);

        return $this;
    }

    /**
     * [getRecursiveSqlCteStatement description]
     * @param string $cteName [description]
     * @param array  &$params [description]
     * @return string
     * @throws exception
     */
    protected function getRecursiveSqlCteStatement(string $cteName, array &$params): string
    {
        $anchorConditionQuery = '';
        if (count($this->recursiveAnchorConditions) > 0) {
            $anchorConditionQuery = 'WHERE ' . sql::convertFilterQueryArray(
                $this->getFilters($this->recursiveAnchorConditions, [], [], $params)
            );
        }

        // Default anchor field name (__anchor)
        // Not to be confused with recursiveAnchorField
        // In contrast to recursive joins, this is more or less static here.
        $anchorFieldName = '__anchor';

        //
        // CTE Prefix / "WITH [RECURSIVE]" is implicitly added by the model class
        //
        return "$cteName "
          . " AS ( "
          . "   SELECT "
          //        We default to the PKEY as (visible) anchor field:
          . "       {$this->getPrimaryKey()} as $anchorFieldName "

          . "     , {$this->getTableIdentifier()}.* "
          . "   FROM {$this->getTableIdentifier()} "
          . "   $anchorConditionQuery "

          //   NOTE: UNION instead of UNION ALL prevents duplicates
          //   and is an implicit termination condition for the recursion
          //   as some query might return rows already selected
          //   leading to 'zero added rows' - and finishing our query
          . "   UNION "

          . "   SELECT "
          . "       $cteName.$anchorFieldName "


          . "     , {$this->getTableIdentifier()}.* "

          . "   FROM {$this->getTableIdentifier()}, $cteName "
          . "   WHERE $cteName.{$this->recursiveSelfReferenceField->get()} = {$this->getTableIdentifier()}.{$this->recursiveAnchorField->get()} "

          . " )";
    }

    /**
     * [convertFilterQueryArray description]
     * @param array $filterQueryArray [description]
     * @return string                   [description]
     */
    public static function convertFilterQueryArray(array $filterQueryArray): string
    {
        $queryPart = '';
        foreach ($filterQueryArray as $index => $filterQuery) {
            if ($index > 0) {
                $queryPart .= ' ' . $filterQuery['conjunction'] . ' ';
            }
            if (is_array($filterQuery['query'])) {
                $queryPart .= self::convertFilterQueryArray($filterQuery['query']);
            } else {
                $queryPart .= $filterQuery['query'];
            }
        }
        return '(' . $queryPart . ')';
    }

    /**
     * Converts the given array of model_plugin_filter instances to the WHERE... query string.
     * Is capable of using $flagfilters for binary operations
     * Handles named filtercollection groups
     * the respective filtercollection(s) (and their filters)
     *
     * returns a recursive array structure that can be converted to a query string
     *
     * @param array $filters [array of filters]
     * @param array $flagfilters [array of flagfilters]
     * @param array $filterCollections [array of filter collections]
     * @param array       &$appliedFilters [cross-model-instance array of currently applied filters, to keep track of PDO variables]
     * @param string|null $currentAlias [current table alias provided during query time]
     * @return array
     * @throws exception
     */
    public function getFilters(array $filters = [], array $flagfilters = [], array $filterCollections = [], array &$appliedFilters = [], ?string $currentAlias = null): array
    {
        $where = [];

        // Loop through each filter
        foreach ($filters as $filter) {
            // collect data for a single filter
            $filterQuery = [
              'conjunction' => $filter->conjunction ?? $this->filterOperator,
              'query' => null,
            ];

            if ($filter instanceof filterInterface) {
                // handle regular filters

                if ($filter instanceof filter) {
                    if (($schema = $filter->field->getSchema()) && ($table = $filter->field->getTable())) {
                        // explicit, fully qualified schema & table
                        $fullQualifier = $this->getTableIdentifier($schema, $table);
                        $filterFieldIdentifier = $filter->getFieldValue($fullQualifier);
                    } else {
                        $filterFieldIdentifier = $filter->getFieldValue($currentAlias);
                    }
                } else {
                    $filterFieldIdentifier = $filter->getFieldValue($currentAlias);
                }

                if (is_array($filter->value)) {
                    // filter value is an array (e.g. IN() match)
                    $values = [];
                    $i = 0;
                    foreach ($filter->value as $thisval) {
                        $var = $this->getStatementVariable(array_keys($appliedFilters), $filterFieldIdentifier, $i++);
                        $values[] = ':' . $var; // var = PDO Param
                        $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field) ?? $this->getFallbackDatatype($thisval)); // values separated from query
                    }
                    $string = implode(', ', $values);
                    $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                    $filterQuery['query'] = $filterFieldIdentifier . ' ' . $operator . ' ( ' . $string . ') ';
                } elseif ($filter->value === null) {
                    // filter value is a singular value
                    // NOTE: $filter->value == 'null' (equality operator, compared to string) may evaluate to TRUE if you're passing in a positive boolean (!)
                    // instead, we're now using the identity operator === to explicitly check for a string 'null'
                    // NOTE: $filter->value == null (equality operator, compared to NULL) may evaluate to TRUE if you're passing in a negative boolean (!)
                    // instead, we're now using the identity operator === to explicitly check for a real NULL
                    // @see http://www.php.net/manual/en/types.comparisons.php

                    // CHANGED 2020-12-30 removed \is_string($filter->value) && \strlen($filter->value) == 0 || $filter->value === 'null'
                    // Which converted '' or 'null' to NULL - which is simply wrong or legacy code.

                    $filterQuery['query'] = $filterFieldIdentifier . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' NULL'; // no param!
                } else {
                    $var = $this->getStatementVariable(array_keys($appliedFilters), $filterFieldIdentifier);
                    $filterQuery['query'] = $filterFieldIdentifier . ' ' . $filter->operator . ' ' . ':' . $var . ' '; // var = PDO Param
                    $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field) ?? 'text'); // values separated from query
                }
            } elseif ($filter instanceof filterlistInterface) {
                $string = is_array($filter->value) ? implode(',', $filter->value) : $filter->value;

                if (strlen($string) !== 0) {
                    if (!preg_match('/^([0-9,]+)$/i', $string)) {
                        throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE, exception::$ERRORLEVEL_ERROR, $filter);
                    }
                    $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                    $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . $operator . ' (' . $string . ') ';
                } else {
                    $filterQuery['query'] = 'false';
                }
            } elseif ($filter instanceof fieldfilter) {
                // handle field-based filters
                // this is not something PDO needs separately transmitted variables for
                // value IS indeed a field name
                // TODO: provide getFieldValue($tableAlias) also for fieldfilters
                $filterQuery['query'] = $filter->getLeftFieldValue($currentAlias) . ' ' . $filter->operator . ' ' . $filter->getRightFieldValue($currentAlias);
            } elseif ($filter instanceof managedFilterInterface) {
                $variableNames = $filter->getFilterQueryParameters();
                $variableNameMap = [];
                foreach ($variableNames as $vName => $vValue) {
                    $variableNameMap[$vName] = $this->getStatementVariable(array_keys($appliedFilters), $vName);
                    $appliedFilters[$variableNameMap[$vName]] = $this->getParametrizedValue($vValue, '');
                }
                $filterQuery['query'] = $filter->getFilterQuery($variableNameMap, $currentAlias);
            }

            // only handle, if query set
            if ($filterQuery['query'] != null) {
                $where[] = $filterQuery;
            } else {
                throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY, exception::$ERRORLEVEL_ERROR, $filter);
            }
        }

        // handle flag filters (bit-oriented)
        foreach ($flagfilters as $flagfilter) {
            // collect data for a single filter
            $filterQuery = [
              'conjunction' => $flagfilter->conjunction ?? $this->filterOperator,
            ];

            $flagVar1 = $this->getStatementVariable(array_keys($appliedFilters), $this->table . '_flag');
            $appliedFilters[$flagVar1] = null; // temporary dummy value
            $flagVar2 = $this->getStatementVariable(array_keys($appliedFilters), $this->table . '_flag');
            $appliedFilters[$flagVar2] = null; // temporary dummy value

            if ($flagfilter < 0) {
                $filterQuery['query'] = $this->table . '_flag & ' . ':' . $flagVar1 . ' <> ' . ':' . $flagVar2 . ' '; // var = PDO Param
                $appliedFilters[$flagVar1] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
                $appliedFilters[$flagVar2] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
            } else {
                $filterQuery['query'] = $this->table . '_flag & ' . ':' . $flagVar1 . ' = ' . ':' . $flagVar2 . ' '; // var = PDO Param
                $appliedFilters[$flagVar1] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
                $appliedFilters[$flagVar2] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
            }

            // we don't have to check for existence of 'query', as it is definitely handled
            // by the previous if-else clause
            $where[] = $filterQuery;
        }

        // collect groups of filter(collections)
        $t_filtergroups = [];

        // Loop through each named group
        foreach ($filterCollections as $groupName => $groupFilterCollection) {
            // handle grouping of filtercollections
            // by default, there's only a single group ( e.g. 'default' )
            $t_groups = [];

            // Loop through each group member (which is a filtercollection) in a named group
            foreach ($groupFilterCollection as $filterCollection) {
                // collect filters in a filtercollection
                $t_filters = [];

                // Loop through each filter in a filtercollection in a named group
                foreach ($filterCollection['filters'] as $filter) {
                    // collect data for a single filter
                    $t_filter = [
                      'conjunction' => $filterCollection['operator'],
                      'query' => null,
                    ];

                    if ($filter instanceof filterInterface) {
                        if ($filter instanceof filter) {
                            if (($schema = $filter->field->getSchema()) && ($table = $filter->field->getTable())) {
                                // explicit, fully qualified schema & table
                                $fullQualifier = $this->getTableIdentifier($schema, $table);
                                $filterFieldIdentifier = $filter->getFieldValue($fullQualifier);
                            } else {
                                $filterFieldIdentifier = $filter->getFieldValue($currentAlias);
                            }
                        } else {
                            $filterFieldIdentifier = $filter->getFieldValue($currentAlias);
                        }

                        if (is_array($filter->value)) {
                            // value is an array
                            $values = [];
                            $i = 0;
                            foreach ($filter->value as $thisval) {
                                $var = $this->getStatementVariable(array_keys($appliedFilters), $filterFieldIdentifier, $i++);
                                $values[] = ':' . $var; // var = PDO Param
                                $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field) ?? $this->getFallbackDatatype($thisval));
                            }
                            $string = implode(', ', $values);
                            $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                            $t_filter['query'] = $filterFieldIdentifier . ' ' . $operator . ' ( ' . $string . ') ';
                        } elseif ($filter->value === null) {
                            // $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                            $t_filter['query'] = $filterFieldIdentifier . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' NULL'; // var = PDO Param
                            // $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                        } else {
                            $var = $this->getStatementVariable(array_keys($appliedFilters), $filterFieldIdentifier);
                            $t_filter['query'] = $filterFieldIdentifier . ' ' . $filter->operator . ' ' . ':' . $var . ' ';
                            $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field) ?? 'text');
                        }
                    } elseif ($filter instanceof managedFilterInterface) {
                        $variableNames = $filter->getFilterQueryParameters();
                        $variableNameMap = [];
                        foreach ($variableNames as $vName => $vValue) {
                            $variableNameMap[$vName] = $this->getStatementVariable(array_keys($appliedFilters), $vName);
                            $appliedFilters[$variableNameMap[$vName]] = $this->getParametrizedValue($vValue, '');
                        }
                        $t_filter['query'] = $filter->getFilterQuery($variableNameMap, $currentAlias);
                    }

                    if ($t_filter['query'] != null) {
                        $t_filters[] = $t_filter;
                    } else {
                        throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY, exception::$ERRORLEVEL_ERROR, $filter);
                    }
                }

                if (count($t_filters) > 0) {
                    // put all collected filters
                    // into a recursive array structure
                    $t_groups[] = [
                      'conjunction' => $filterCollection['conjunction'] ?? $this->filterOperator,
                      'query' => $t_filters,
                    ];
                }
            }

            // put all collected filtercollections in the named group
            // into a recursive array structure
            $t_filtergroups[] = [
              'group_name' => $groupName,
              'conjunction' => $this->filterOperator,
              'query' => $t_groups,
            ];
        }

        if (count($t_filtergroups) > 0) {
            // put all collected named groups
            // into a recursive array structure
            $where[] = [
              'conjunction' => $this->filterOperator,
              'query' => $t_filtergroups,
            ];
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
     * returns an estimated core-framework datatype for a given value
     * in case there's no definitive datatype specified
     * @param mixed $value
     * @return string|null
     * @throws exception
     */
    protected function getFallbackDatatype(mixed $value): ?string
    {
        if ($value === null) {
            return null; // unspecified
        } elseif (is_int($value)) {
            return 'number_natural';
        } elseif (is_float($value)) {
            return 'number';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_string($value)) {
            return 'text';
        }
        throw new exception('INVALID_FALLBACK_PARAMETER_TYPE', exception::$ERRORLEVEL_ERROR);
    }

    /**
     * [getFilterQuery description]
     * @param array        &$appliedFilters [reference array containing used filters]
     * @param string|null $mainAlias [provide an alias for the main table]
     * @return string
     * @throws exception
     */
    public function getFilterQuery(array &$appliedFilters = [], ?string $mainAlias = null): string
    {
        // provide pseudo main table alias, if needed
        $filterQueryArray = $this->getFilterQueryComponents($appliedFilters, $mainAlias);

        //
        // HACK: re-join filter groups
        //
        $grouped = [];
        $ungrouped = [];
        foreach ($filterQueryArray as $part) {
            if (is_array($part['query'])) {
                //
                // restructure part
                //
                $rePart = [
                  'conjunction' => $part['conjunction'],
                  'query' => [],
                ];
                foreach ($part['query'] as $queryComponent) {
                    if ($queryComponent['group_name'] ?? false) {
                        $grouped[$queryComponent['group_name']] = $grouped[$queryComponent['group_name']] ?? [];
                        $grouped[$queryComponent['group_name']]['group_name'] = $queryComponent['group_name'];
                        $grouped[$queryComponent['group_name']]['conjunction'] = $queryComponent['conjunction']; // this resets it every time
                        $grouped[$queryComponent['group_name']]['query'] = array_merge($grouped[$queryComponent['group_name']]['query'] ?? [], $queryComponent['query']);
                    } else {
                        $rePart['query'][] = $queryComponent;
                    }
                }
                if (count($rePart['query']) > 0) {
                    $ungrouped[] = $rePart;
                }
            } else {
                $ungrouped[] = $part;
            }
        }

        $filterQueryArray = array_merge($ungrouped, array_values($grouped));

        if ($this->saveLastFilterQueryComponents) {
            $this->lastFilterQueryComponents = $filterQueryArray;
        }
        if (count($filterQueryArray) > 0) {
            return ' WHERE ' . self::convertFilterQueryArray($filterQueryArray);
        } else {
            return '';
        }
    }

    /**
     * [getFilterQueryComponents description]
     * @param array  &$appliedFilters [description]
     * @param string|null $currentAlias
     * @return array
     * @throws exception
     */
    public function getFilterQueryComponents(array &$appliedFilters = [], string $currentAlias = null): array
    {
        $where = $this->getFilters($this->filter, $this->flagfilter, $this->filterCollections, $appliedFilters, $currentAlias);

        // get filters from nested models recursively
        foreach ($this->nestedModels as $join) {
            if ($this->compatibleJoin($join->model)) {
                $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters, $join->currentAlias));
            }
        }

        return $where;
    }

    /**
     * {@inheritDoc}
     */
    protected function compatibleJoin(model $model): bool
    {
        return parent::compatibleJoin($model) && ($this->db === $model->db);
    }

    /**
     * [deepJoin description]
     * @param model $model [model currently worked-on]
     * @param array                  &$tableUsage [table usage as reference]
     * @param int                    &$aliasCounter [alias counter as reference]
     * @param string|null $parentAlias
     * @param array                  &$params
     * @param array                  &$cte [common table expressions, if any]
     * @return string                 [query part]
     * @throws exception
     */
    public function deepJoin(model $model, array &$tableUsage = [], int &$aliasCounter = 0, string $parentAlias = null, array &$params = [], array &$cte = []): string
    {
        if (count($model->getNestedJoins()) == 0) {
            return '';
        }
        $ret = '';

        // Loop through nested (children/parents)
        foreach ($model->getNestedJoins() as $join) {
            $nest = $join->model;

            // check model joining compatible
            if (!$model->compatibleJoin($nest)) {
                continue;
            }

            $alias = null;
            $aliasAs = '';
            $cteName = null;

            // preliminary CTE, model itself is recursive
            // $cteName = null;
            if ($nest->recursive) {
                $cteName = '__cte_recursive_' . (count($cte) + 1);
                $cte[] = $nest->getRecursiveSqlCteStatement($cteName, $params);
                $join->referenceField = '__anchor';
                $tableUsage[$cteName] = 1;
                // Also increase this counter, though this is a CTE
                // to correctly keep track of ambiguous fields
                $tableUsage["$nest->schema.$nest->table"]++;
                $alias = $cteName;
            }


            if ($nest->recursive || $join instanceof recursive) {
                //
                // 'WITH ... RECURSIVE' CTE support
                //
                if ($join instanceof sqlCteStatementInterface) {
                    $cteAlias = $cteName; // if table is already a CTE, passthrough
                    $cteName = '__cte_recursive_' . (count($cte) + 1);
                    if (array_key_exists($cteName, $tableUsage)) {
                        // name collision
                        throw new exception('MODEL_SCHEMATIC_SQL_DEEP_JOIN_CTE_NAME_COLLISION', exception::$ERRORLEVEL_ERROR, $cteName);
                    } else {
                        $tableUsage[$cteName] = 1;
                        // Also increase this counter, though this is a CTE
                        // to correctly keep track of ambiguous fields
                        $tableUsage["$nest->schema.$nest->table"]++;
                    }
                    $cte[] = $join->getSqlCteStatement($cteName, $params, $cteAlias);
                    $alias = $cteName;
                    $aliasAs = "AS " . $alias;
                } elseif ($join instanceof recursive) {
                    throw new exception('MODEL_SCHEMATIC_SQL_DEEP_JOIN_UNSUPPORTED_JOIN_RECURSIVE_PLUGIN', exception::$ERRORLEVEL_ERROR, get_class($join));
                }
            } elseif (array_key_exists("$nest->schema.$nest->table", $tableUsage)) {
                $aliasCounter++;
                $tableUsage["$nest->schema.$nest->table"]++;
                $alias = "a" . $aliasCounter;
                $aliasAs = "AS " . $alias;
            } else {
                $tableUsage["$nest->schema.$nest->table"] = 1;

                if ($nest->isDiscreteModel()) {
                    //
                    // CHANGED/ADDED 2020-06-10
                    // derived table, explicitly specify alias
                    // for usage with discrete model feature
                    // This is needed in the case of ONE/the first join of this derived table
                    //
                    $aliasAs = $nest->table;
                }
                $alias = $nest->getTableIdentifier();
            }


            // get join method from plugin
            $joinMethod = $join->getJoinMethod();

            // if $joinMethod == null == DEFAULT -> use current config.
            // this should be deprecated or removed...
            if ($joinMethod == null) {
                $joinMethod = "LEFT JOIN";
                if ($this->rightJoin) {
                    $joinMethod = "RIGHT JOIN";
                }
            }

            $thisKey = $join->modelField;
            $joinKey = $join->referenceField;

            if (($thisKey == null) || ($joinKey == null)) {
                //
                // CHANGED/ADDED 2020-06-10
                // We allow thisKey & joinKey to be null (models not directly in relation)
                // In this case, additional conditions have to be defined
                // See else
                //
                if (!$this->isDiscreteModel() && !$join->model->isDiscreteModel()) {
                    throw new exception(self::EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG, exception::$ERRORLEVEL_FATAL, [$this->table, $nest->table]);
                } elseif (!$join->conditions || count($join->conditions) === 0) {
                    throw new exception(self::EXCEPTION_SQL_DEEPJOIN_INVALID_FOREIGNKEY_CONFIG, exception::$ERRORLEVEL_FATAL, [$this->table, $nest->table]);
                }
            }

            $joinComponents = [];

            $useAlias = $parentAlias ?? $this->getTableIdentifier(); // $this->table;

            if ($thisKey === null && $joinKey === null) {
                // only rely on conditions
//                $cAlias = $alias ?? $useAlias; // TODO: dunno if this is correct. test also reverse and forward joins
            } elseif (is_array($thisKey) && is_array($joinKey)) {
                // TODO: check for equal array item counts! otherwise: exception
                // perform a multi-component join
                foreach ($thisKey as $index => $thisKeyValue) {
                    $joinComponents[] = "$alias.$joinKey[$index] = $useAlias.$thisKeyValue";
                }
            } elseif (is_array($thisKey) && !is_array($joinKey)) {
                foreach ($thisKey as $index => $thisKeyValue) {
                    $joinComponents[] = "$alias.$index = $useAlias.$thisKeyValue";
                }
            } elseif (!is_array($thisKey) && is_array($joinKey)) {
                throw new LogicException('Not implemented multi-component foreign key join');
            } else {
                $joinComponents[] = "$alias.$joinKey = $useAlias.$thisKey";
            }

            // Determine the specific alias
            // if we're doing a reverse join, current alias is simply wrong
            // at least when using explicit values as condition parts
            // NOTE/CHANGED 2020-09-15: for custom joins, this is wrong
            // as the 'opposite site' also doesn't have a fkey reference.
            $cAlias = null;
            if (!is_array($joinKey) && ($nest->getConfig()->get('foreign>' . $joinKey . '>key') == $thisKey)) {
                //
                // Back-reference, validated by checking the existence
                // of an FKEY config in the nested ref back to THIS model
                //
                $cAlias = $alias;
            } elseif (!is_array($thisKey) && ($this->getConfig()->get('foreign>' . $thisKey . '>key') == $joinKey)) {
                //
                // Forward reference, validated by checking the existence
                // of an FKEY config in THIS model to the nested one
                //
                $cAlias = $useAlias;
            } else {
                // neither this, nor nested model has a fkey ref - this is a custom join!
                $cAlias = $alias;
            }


            // add conditions!
            foreach ($join->conditions as $filter) {
                $operator = $filter['value'] == null ? ($filter['operator'] == '!=' ? 'IS NOT' : 'IS') : $filter['operator'];

                //
                // NOTE/IMPORTANT:
                // At the moment, we explicitly DO NOT support PDO Params in conditions
                // as we also specify conditions referring to fields instead of values
                //
                $value = $filter['value'] == null ? 'NULL' : $filter['value'];

                $tAlias = $cAlias;

                //
                // ADDED 2020-09-15 Allow explicit model name for conditions
                // To allow filters on both sides
                //
                if ($filter['model_name'] ?? false) {
                    // explicit model override in filter dataset
                    if ($filter['model_name'] == $this->getIdentifier()) {
                        $tAlias = $useAlias;
                    } elseif ($filter['model_name'] == $nest->getIdentifier()) {
                        $tAlias = $alias;
                    } else {
                        throw new exception('INVALID_JOIN_CONDITION_MODEL_NAME', exception::$ERRORLEVEL_ERROR);
                    }
                }

                $joinComponents[] = ($tAlias ? $tAlias . '.' : '') . "{$filter['field']} $operator $value";
            }

            $joinComponentsString = implode(' AND ', $joinComponents);

            // SQL USE INDEX implementation, limited to one index per table at a time
            $useIndex = '';
            if (($nest->useIndex ?? false) && count($nest->useIndex) > 0) {
                $useIndex = ' USE INDEX(' . $nest->useIndex[0] . ') ';
            }

            //
            // CHANGED/ADDED 2020-06-10 Discrete models (empowering sub queries)
            // NOTE: we're checking for discrete models here
            // as they don't represent a table on its own, but merely an entire subquery
            //
            if ($cteName !== null) {
                $ret .= " $joinMethod $cteName $aliasAs$useIndex ON $joinComponentsString";
            } elseif ($nest->isDiscreteModel() && $nest instanceof discreteModelSchematicSqlInterface) {
                $ret .= " $joinMethod {$nest->getDiscreteModelQuery($params)} $aliasAs$useIndex ON $joinComponentsString";
            } else {
                $ret .= " $joinMethod {$nest->getTableIdentifier()} $aliasAs$useIndex ON $joinComponentsString";
            }

            // CHANGED 2020-11-26: set alias or fallback to table name, by default
            // To ensure correct duplicate field name handling across multiple tables
            // CHANGED again: we have to leave this null, if no alias.
            // This crashes filter methods, as it overrides the alias in any aspect.
            // NOTE: we might have to include schema name, too.
            $join->currentAlias = $alias; // ?? $nest->table;

            $ret .= $nest->deepJoin($nest, $tableUsage, $aliasCounter, $join->currentAlias, $params, $cte);
        }

        return $ret;
    }

    /**
     * custom wrapping override due to PG's case sensitivity
     * @param string $identifier [description]
     * @return string             [description]
     * @throws exception
     */
    protected function wrapIdentifier(string $identifier): string
    {
        return $this->getServicingSqlInstance()->wrapIdentifier($identifier);
    }

    /**
     * Returns the current fieldlist as an array of triples (schema, table, field)
     * it contains the visible fields of all nested models
     * retrieved in a recursive call
     * this also respects hiddenFields
     *
     * @param string|null $alias [optional: alias as prefix for the following fields - table alias!]
     * @param array &$params [optional: current pdo params, including values]
     * @return array
     * @throws exception
     */
    protected function getCurrentFieldlist(?string $alias, array &$params): array
    {
        // CHANGED 2019-06-17: main functionality moved to ::getCurrentFieldlistNonRecursive
        // as we also need it for each model, singularly in ::getVirtualFieldResult()
        $result = $this->getCurrentFieldlistNonRecursive($alias, $params);

        foreach ($this->nestedModels as $join) {
            if ($this->compatibleJoin($join->model)) {
                $result = array_merge($result, $join->model->getCurrentFieldlist($join->currentAlias, $params));
            }
        }

        return $result;
    }

    /**
     * retrieves the fieldlist of this model
     * on a non-recursive basis
     *
     * @param string|null $alias [description]
     * @param array        &$params [description]
     * @return array                  [description]
     * @throws exception
     */
    protected function getCurrentFieldlistNonRecursive(?string $alias, array &$params): array
    {
        $result = [];
        if (count($this->fieldlist) == 0 && count($this->hiddenFields) > 0) {
            //
            // Include all fields but specific ones
            //
            foreach ($this->getFields() as $fieldName) {
                if ($this->config->get('datatype>' . $fieldName) !== 'virtual') {
                    if (!in_array($fieldName, $this->hiddenFields)) {
                        if ($alias != null) {
                            $result[] = [$alias, $this->wrapIdentifier($fieldName)];
                        } else {
                            $result[] = [$this->getTableIdentifier($this->schema, $this->table), $this->wrapIdentifier($fieldName)];
                        }
                    }
                }
            }
        } elseif (count($this->fieldlist) > 0) {
            //
            // Explicit field list
            //
            foreach ($this->fieldlist as $field) {
                if ($field instanceof calculatedfieldInterface) {
                    //
                    // custom field calculation
                    //
                    $result[] = [$field->get()];
                } elseif ($field instanceof aggregateInterface) {
                    //
                    // pre-defined aggregate function
                    //
                    $result[] = [$field->get($alias)];
                } elseif ($field instanceof fulltextInterface) {
                    //
                    // pre-defined aggregate function
                    //

                    $var = $this->getStatementVariable(array_keys($params), $field->getField(), '_ft');
                    $params[$var] = $this->getParametrizedValue($field->getValue(), 'text');
                    $result[] = [$field->get($var, $alias)];
                } elseif ($this->config->get('datatype>' . $field->field->get()) !== 'virtual' && (!in_array($field->field->get(), $this->hiddenFields) || $field->alias)) {
                    //
                    // omit virtual fields
                    // they're not part of the DB.
                    //
                    $fieldAlias = $field->alias?->get();
                    if ($alias != null) {
                        if ($fieldAlias) {
                            $result[] = [$alias, $this->wrapIdentifier($field->field->get()) . ' AS ' . $this->wrapIdentifier($fieldAlias)];
                        } else {
                            $result[] = [$alias, $this->wrapIdentifier($field->field->get())];
                        }
                    } elseif ($fieldAlias) {
                        $result[] = [$this->getTableIdentifier($field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table), $this->wrapIdentifier($field->field->get()) . ' AS ' . $this->wrapIdentifier($fieldAlias)];
                    } else {
                        $result[] = [$this->getTableIdentifier($field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table), $this->wrapIdentifier($field->field->get())];
                    }
                }
            }

            //
            // add the rest of the data-model-defined fields
            // as long as they're not hidden.
            //
            foreach ($this->getFields() as $fieldName) {
                if ($this->config->get('datatype>' . $fieldName) !== 'virtual') {
                    if (!in_array($fieldName, $this->hiddenFields)) {
                        if ($alias != null) {
                            $result[] = [$alias, $this->wrapIdentifier($fieldName)];
                        } else {
                            $result[] = [$this->getTableIdentifier($this->schema, $this->table), $this->wrapIdentifier($fieldName)];
                        }
                    }
                }
            }

            //
            // NOTE:
            // array_unique can be used on arrays that contain objects or sub-arrays
            // you need to use SORT_REGULAR for this case (!)
            //
            $result = array_unique($result, SORT_REGULAR);
        } elseif (count($this->hiddenFields) === 0) {
            //
            // The rest of the fields. Simply using a wildcard
            //
            if ($alias != null) {
                $result[] = [$alias, '*'];
            } else {
                $result[] = [$this->getTableIdentifier($this->schema, $this->table), '*'];
            }
        }

        return $result;
    }

    /**
     * Returns grouping components
     * @param string|null $currentAlias
     * @return array
     * @throws exception
     */
    public function getGroups(string $currentAlias = null): array
    {
        $groupArray = [];

        // group by fields
        foreach ($this->group as $group) {
            if ($group->aliased) {
                $groupArray[] = $this->wrapIdentifier($group->field->get());
            } elseif (!$currentAlias) {
                $groupArray[] = implode(
                    '.',
                    array_filter([
                      $this->getTableIdentifier($group->field->getSchema() ?? null, $group->field->getTable() ?? null),
                      $this->wrapIdentifier($group->field->get()),
                    ])
                );
            } else {
                $groupArray[] = implode(
                    '.',
                    array_filter([
                      $currentAlias,
                      $this->wrapIdentifier($group->field->get()),
                    ])
                );
            }
        }

        foreach ($this->getNestedJoins() as $join) {
            if ($join->model instanceof sql) {
                $groupArray = array_merge($groupArray, $join->model->getGroups($join->currentAlias));
            }
        }

        return $groupArray;
    }

    /**
     * [getAggregateQueryComponents description]
     * @param array &$appliedFilters [description]
     * @return array                 [description]
     * @throws exception
     */
    public function getAggregateQueryComponents(array &$appliedFilters = []): array
    {
        $aggregate = $this->getFilters($this->aggregateFilter, [], [], $appliedFilters);

        // get filters from nested models recursively
        foreach ($this->nestedModels as $join) {
            if ($this->compatibleJoin($join->model)) {
                $aggregate = array_merge($aggregate, $join->model->getAggregateQueryComponents($appliedFilters));
            }
        }

        return $aggregate;
    }

    /**
     * Converts the given array of model_plugin_order instances to the ORDER BY... query string
     * @param array $orders
     * @return string
     * @throws exception
     */
    protected function getOrders(array $orders): string
    {
        // defaults
        $order = '';
        $appliedOrders = 0;

        // order fields
        foreach ($orders as $myOrder) {
            $order .= ($appliedOrders > 0) ? ', ' : ' ORDER BY ';

            $schema = $myOrder->field->getSchema();
            $table = $myOrder->field->getTable();
            $field = $myOrder->field->get();

            $specifier = [];
            if ($schema && $table) {
                $specifier[] = $this->getServicingSqlInstance()->getTableIdentifierParametrized($schema, $table);
            } elseif ($table) {
                $specifier[] = $table;
            } else {
                // might be local alias
            }
            $specifier[] = $this->wrapIdentifier($field);

            $order .= implode('.', $specifier) . ' ' . $myOrder->direction . ' ';
            $appliedOrders++;
        }

        return $order;
    }

    /**
     * Converts the given instance of model_plugin_limit to the LIMIT... query string
     * @param limit $limit
     * @return string
     */
    protected function getLimit(limit $limit): string
    {
        if ($limit->limit > 0) {
            return " LIMIT " . $limit->limit . " ";
        }
        return '';
    }

    /**
     * Converts the given instance of model_plugin_offset to the OFFSET... query string
     * @param offset $offset
     * @return string
     */
    protected function getOffset(offset $offset): string
    {
        if ($offset->offset > 0) {
            return " OFFSET " . $offset->offset . " ";
        }
        return '';
    }

    /**
     *
     * {@inheritDoc}
     * @param mixed|null $primaryKey
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\modelInterface::delete($primaryKey)
     */
    public function delete(mixed $primaryKey = null): model
    {
        if (!is_null($primaryKey)) {
            $this->deleteChildren($primaryKey);

            if ($this->useTimemachine()) {
                $tm = timemachine::getInstance($this->getIdentifier());
                $tm->saveState($primaryKey, [], true); // supply empty array and deletion flag
            }

            $query = "DELETE FROM " . $this->getTableIdentifier() . " WHERE " . $this->getPrimaryKey() . " = " . $primaryKey;
            $this->doQuery($query);
            return $this;
        }

        if (count($this->filter) == 0) {
            throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED', exception::$ERRORLEVEL_FATAL);
        }

        //
        // New method: use the filterquery to construct a single query delete statement
        //

        $query = "DELETE FROM " . $this->getTableIdentifier() . ' ';

        // from search()
        // prepare an array for values to submit as PDO statement parameters
        // done by-ref, so the values are arriving right here after
        // running getFilterQuery()
        $params = [];

        // pre-fetch filterquery for regular query and timemachine
        $filterQuery = $this->getFilterQuery($params);

        //
        // query the dataset's pkey identifiers that are to-be-deleted
        // and submit each to timemachine
        //
        if ($this->useTimemachine()) {
            $timemachineQuery = "SELECT {$this->getPrimaryKey()} FROM " . $this->getTableIdentifier() . ' ';
            $timemachineQuery .= $filterQuery;
            $this->internalQuery($timemachineQuery, $params);
            $timemachineResult = $this->db->getResult();
            $pkeyValues = array_column($timemachineResult, $this->getPrimaryKey());

            $tm = timemachine::getInstance($this->getIdentifier());
            foreach ($pkeyValues as $id) {
                $tm->saveState($id, [], true); // supply empty array and deletion flag
            }
        }

        $query .= $filterQuery;
        $this->doQuery($query, $params);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @param string $query
     * @param array $params
     * @throws ReflectionException
     * @throws exception
     */
    protected function internalQuery(string $query, array $params = []): void
    {
        // perform internal query
        $this->db->query($query, $params);
    }

    /**
     * the current database connection instance
     * @return database [description]
     */
    public function getConnection(): database
    {
        return $this->db;
    }

    /**
     * enables overriding/setting the connection
     * @param database $db [description]
     */
    public function setConnectionOverride(database $db): void
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     * @return int
     * @throws ReflectionException
     * @throws exception
     */
    public function getCount(): int
    {
        //
        // Russian Caviar Begin
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        $this->countingModeOverride = true;

        $this->search();
        $count = $this->db->getResult()[0]['___count'] ?? 0;

        //
        // Russian Caviar End
        //
        $this->countingModeOverride = false;
        return $count;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        if (!$this->countingModeOverride) {
            parent::reset();
        } else {
            // do not reset everything, if we're in special counting mode.
            // just reset errorstack.
            $this->errorstack->reset();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addUseIndex(array $fields): model
    {
        $fieldString = (count($fields) === 1 ? $fields[0] : implode(',', $fields));
        $this->useIndex = ['index_' . md5($fieldString)];
        // $this->useIndex = array_values(array_unique($this->useIndex));
        return $this;
    }

    /**
     * performs a create or replace (update)
     * @param array $data [description]
     * @return model   [this instance]
     * @throws ReflectionException
     * @throws exception
     */
    public function replace(array $data): model
    {
        $params = [];
        $query = $this->saveCreate($data, $params, true); // saveCreate with $replace = true
        $this->doQuery($query, $params);
        return $this;
    }

    /**
     * performs an update using the current filters and a given data array
     * @param array $data [description]
     * @return model          [this instance]
     * @throws ReflectionException
     * @throws exception
     */
    public function update(array $data): model
    {
        if (count($this->filter) == 0) {
            throw new exception('EXCEPTION_MODEL_SCHEMATIC_SQL_UPDATE_NO_FILTERS_DEFINED', exception::$ERRORLEVEL_FATAL);
        }
        $query = 'UPDATE ' . $this->getTableIdentifier() . ' SET ';
        $parts = [];

        $param = [];
        foreach ($this->config->get('field') as $field) {
            if (in_array($field, [$this->getPrimaryKey(), $this->table . "_modified", $this->table . "_created"])) {
                continue;
            }

            // If it exists, set the field
            if (array_key_exists($field, $data)) {
                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }

                $var = $this->getStatementVariable(array_keys($param), $field);

                // performance hack: store modelfield instance!
                if (!isset($this->modelfieldInstance[$field])) {
                    $this->modelfieldInstance[$field] = modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                $parts[] = $field . ' = ' . ':' . $var;
            }
        }

        if ($this->saveUpdateSetModifiedTimestamp) {
            $parts[] = $this->table . "_modified = " . $this->getServicingSqlInstance()->getSaveUpdateSetModifiedTimestampStatement($this);
        }
        $query .= implode(',', $parts);

        // $params = [];
        $filterQuery = $this->getFilterQuery($param);

        //
        // query the dataset's pkey identifiers that are to-be-updated
        // and submit each to timemachine
        //
        if ($this->useTimemachine()) {
            $timemachineQuery = "SELECT {$this->getPrimaryKey()} FROM " . $this->getTableIdentifier() . ' ';
            // NOTE: we have to use a separate array for this
            // as we're also storing bound params of the update data in $param above
            $timemachineFilterQueryParams = [];
            $timemachineFilterQuery = $this->getFilterQuery($timemachineFilterQueryParams);
            $timemachineQuery .= $timemachineFilterQuery;
            $this->internalQuery($timemachineQuery, $timemachineFilterQueryParams);
            $timemachineResult = $this->db->getResult();
            $pkeyValues = array_column($timemachineResult, $this->getPrimaryKey());

            $tm = timemachine::getInstance($this->getIdentifier());
            foreach ($pkeyValues as $id) {
                $tm->saveState($id, $data); // supply data to be changed for each entry
            }
        }

        $query .= $filterQuery;
        $this->doQuery($query, $param);

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::copy($primaryKey)
     */
    public function copy(mixed $primaryKey): model
    {
        return $this;
    }

    /**
     *
     * @param string $operator
     * @return postgresql
     */
    public function setOperator(string $operator): model
    {
        $this->filterOperator = $operator;
        return $this;
    }

    /**
     * [setSaveLastFilterQueryComponents description]
     * @param bool $state [description]
     */
    public function setSaveLastFilterQueryComponents(bool $state): void
    {
        $this->saveLastFilterQueryComponents = $state;
    }

    /**
     * [getLastFilterQueryComponents description]
     * @return array|null
     */
    public function getLastFilterQueryComponents(): ?array
    {
        return $this->lastFilterQueryComponents;
    }

    /**
     * {@inheritDoc}
     * @see \codename\core\model_interface::withFlag($flagval)
     */
    public function withFlag(int $flagval): model
    {
        if (!in_array($flagval, $this->flagfilter)) {
            $this->flagfilter[] = $flagval;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutFlag(int $flagval): model
    {
        $flagval = $flagval * -1;
        if (!in_array($flagval, $this->flagfilter)) {
            $this->flagfilter[] = $flagval;
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::withDefaultFlag($flagval)
     */
    public function withDefaultFlag(int $flagval): model
    {
        if (!in_array($flagval, $this->defaultflagfilter)) {
            $this->defaultflagfilter[] = $flagval;
        }
        $this->flagfilter = array_merge($this->defaultflagfilter, $this->flagfilter);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutDefaultFlag(int $flagval): model
    {
        $flagval = $flagval * -1;
        if (!in_array($flagval, $this->defaultflagfilter)) {
            $this->defaultflagfilter[] = $flagval;
        }
        $this->flagfilter = array_merge($this->defaultflagfilter, $this->flagfilter);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @param string $transactionName
     * @throws exception
     */
    public function beginTransaction(string $transactionName): void
    {
        $this->db->beginVirtualTransaction($transactionName);
    }

    /**
     * {@inheritDoc}
     * @param string $transactionName
     * @throws exception
     */
    public function endTransaction(string $transactionName): void
    {
        $this->db->endVirtualTransaction($transactionName);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCurrentCacheIdentifierParameters(): array
    {
        $params = parent::getCurrentCacheIdentifierParameters();
        //
        // extend cache params by the virtual field result setting
        //
        $params['virtualfieldresult'] = $this->virtualFieldResult;
        return $params;
    }

    /**
     * {@inheritDoc}
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function internalGetResult(): array
    {
        $result = $this->db->getResult();
        if ($this->virtualFieldResult) {
            // echo("<pre>" . print_r($result, true) . "</pre>");

            $tResult = $this->getVirtualFieldResult($result);

            $result = $this->normalizeRecursivelyByFieldlist($tResult);

            //
            // Root element virtual fields
            //
            if (count($this->virtualFields) > 0) {
                foreach ($result as &$d) {
                    // NOTE: at the moment, we already handle virtual fields
                    // (e.g. a field added through ->addVirtualField)
                    // in ->getVirtualFieldResult(...)
                    // at the end, when we reached the original root structure again.

                    //
                    // NOTE/CHANGED 2019-09-10: we now handle virtual fields for the root model right here
                    // as we wouldn't get normalized structure fields the way we did it before,
                    //
                    // we were handling virtual fields inside ::getVirtualFieldResult()
                    // Which DOES NOT normalize those fields - so, inside a virtualField callback, you'd get JSON strings
                    // instead of "real" object/array data
                    //
                    $d = $this->handleVirtualFields($d);
                }
            }
        }
        return $result;
    }

    /**
     * [getVirtualFieldResult description]
     * @param array $result [the original resultset]
     * @param array  &$track [array keeping track of model index/instances]
     * @param array $structure
     * @param array $trackFields
     * @return array [type]             [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function getVirtualFieldResult(array $result, array &$track = [], array $structure = [], array &$trackFields = []): array
    {
        // Construct field tracking array
        // by diving into the whole structure beforehand
        // one single time
        if (count($trackFields) === 0) {
            $this->populateTrackFieldsRecursive($trackFields);
        }

        foreach ($this->getNestedJoins() as $join) {
            //
            // NOTE/CHANGED 2020-09-15: exclude "incompatible" models from tracking
            // this includes forced virtual joins, as they HAVE to be excluded
            // to avoid an 'obiwan' or similar error - index-based re-association
            // for virtual resultsets. We treat a forced virtual join as an 'incompatible' model / 'blackbox'
            //
            if ($this->compatibleJoin($join->model)) {
                $track[$join->model->getIdentifier()][] = $join->model;
            }

            if ($join->model instanceof virtualFieldResultInterface) {
                $structureDive = [];

                // if virtualFieldResult enabled on this model
                // use vField config from join plugin
                if ($this->virtualFieldResult) {
                    if ($join->virtualField) {
                        $structureDive = [$join->virtualField];
                    }
                }

                //
                // NOTE/CHANGED 2020-09-15: handle (in-)compatible joins separately
                // As stated above, this is for forced virtual joins - we have to treat them as 'incompatible' models
                // to avoid index confusion. At this point, we reset the tracking/structure dive,
                // as we're 'virtually' diving into a different model resultset
                //
                if ($this->compatibleJoin($join->model)) {
                    $result = $join->model->getVirtualFieldResult($result, $track, array_merge($structure, $structureDive), $trackFields);
                } else {
                    //
                    // CHANGED 2021-04-13: kicked out, as it does not apply
                    // NOTE: we should keep an eye on this.
                    // At this point, we're not calling getVirtualFieldResult, as we either have
                    // - a completely different model technology
                    // - a forced virtual join
                    // - sth. else?
                    //
                    // >>> Those models handle their results for themselves.
                    //
                    // $result = $join->model->getVirtualFieldResult($result);
                }
            }
        }

        //
        // Re-normalizing joined data
        // This is a completely different approach
        // instead of iterating over all vField/children-supporting models
        // We iterate over all models - as we have to handle mixed cases, too.
        //
        // CHANGED 2021-04-13, we include $this (current model)
        // to propage/include renormalization for root model
        // (e.g. if joining the same model recursively)
        //
        $subjects = array_merge([$this], $this->getNestedJoins());

        foreach ($subjects as $join) {
            $vModel = null;
            $virtualField = null;
            // Re-name/alias the current join model instance
            if ($join === $this) {
                $vModel = $join; // this (model), root model renormalization
            } else {
                $vModel = $join->model;
                if ($this->virtualFieldResult) {
                    // handle $join->virtualField
                    $virtualField = $join->virtualField;
                }
            }

            $index = null;
            if (count($indexes = array_keys($track[$vModel->getIdentifier()] ?? [], $vModel, true)) === 1) {
                $index = $indexes[0];
            } else {
                // What happens, if we join the same model instance twice or more?
            }

            if ($index === null) {
                // index is still null -> model not found in currently nested models
                // TODO: we might check for virtual field result or so?
                // continue;
            }

            $vModelFieldlist = $vModel->getCurrentAliasedFieldlist();
            $fields = $vModelFieldlist;

            // determine per-field indexes
            // as we might join the same model
            // with differing field lists
            $fieldValueIndexes = [];

            foreach ($fields as $modelField) {
                $index = null;

                if ($trackFields[$modelField] ?? false) {
                    if (count($trackFields[$modelField]) === 1) {
                        // There's only a single occurrence of this modelfield
                        // Index is being unset.
                    } elseif ($vModel->getFieldtype(modelfield::getInstance($modelField)) === 'virtual') {
                        // Avoid virtual fields
                        // as we're handling them differently
                        // And they're not SQL-native.
                        $index = false; // null; // QUESTION: or false?
                    } elseif (count($indexes = array_keys($trackFields[$modelField], $vModel, true)) === 1) { // NOTE/CHANGED: $vModel was $join->model before - which is an iteration variable from above!
                        // this is the expected field index
                        // when re-normalizing from a FETCH_NAMED PDO result
                        $index = $indexes[0];
                    }
                }

                $fieldValueIndexes[$modelField] = $index;
            }

            //
            // Iterate over each dataset of the result
            // And apply index renormalization (reversing FETCH_NAMED-based array-style results)
            //
            foreach ($result as &$dataset) {
                $vData = [];

                foreach ($fields as $modelField) {
                    if (($vIndex = $fieldValueIndexes[$modelField]) !== null) {
                        // DEBUG Just a test for vIndex === false when working on virtual field
                        // Doesn't work?
                        // NOTE: this might have an effect to unsetting virtual fields based on joins
                        // to NOT display if the respective models are not joined. Hopefully.
                        // Doesn't apply to the root model, AFAICS.
                        if ($vIndex === false) {
                            continue;
                        }

                        // Use index reference determined above
                        $vData[$modelField] = $dataset[$modelField][$vIndex] ?? null;
                    } else {
                        // Simply use the field value
                        $vData[$modelField] = $dataset[$modelField] ?? null;
                    }
                }

                // Normalize the data against the respective vModel
                $vData = $vModel->normalizeRow($vData);

                // Deep dive to set data in a sub-object path
                // $structure might be [], which is simply the root level
                $dive = &$dataset;
                foreach ($structure as $key) {
                    $dive[$key] = $dive[$key] ?? [];
                    $dive = &$dive[$key];
                }

                if ($virtualField !== null) {
                    // NOTE: Forward merging is bad for this case
                    // as array_merge overwrites existing keys with the latter one
                    // in this case, $dive[$virtualField] contains partial data
                    // which we HAVE to overwrite, in regard to $vData

                    $dive[$virtualField] = array_merge($vData, $dive[$virtualField] ?? []);
                } else {
                    // NOTE: Forward merge
                    // as $vData contains new information to be overwritten in $dive,
                    // as far as applicable. See note above.
                    $dive = array_merge($dive ?? [], $vData);
                }

                // handle custom virtual fields
                // CHANGED 2019-06-05: we have to trigger virtual field handling
                // AFTER diving, because we might be missing all the important fields...
                // CHANGED 2020-11-13: we additionally have to check for vModel being 'compatible'
                // e.g. JSON data model's virtual fields won't be handled here - causes bugs.
                if ($this->compatibleJoin($vModel) && count($vModel->getVirtualFields()) > 0) {
                    if ($virtualField !== null) {
                        $dive[$virtualField] = $vModel->handleVirtualFields($dive[$virtualField]);
                    } else {
                        $dive = $vModel->handleVirtualFields($dive);
                    }
                }
            }
        }

        if (($children = $this->config->get('children')) != null) {
            foreach ($children as $field => $config) {
                if ($config['type'] === 'collection') {
                    // check for active collectionmodel / plugin
                    if (isset($this->collectionPlugins[$field])) {
                        $collection = $this->collectionPlugins[$field];
                        $vModel = $collection->collectionModel;

                        // determine to-be-used index for THIS model, as it is the base for the collection?
                        // $index =
                        $index = null;

                        if ((!isset($track[$this->getIdentifier()])) || count($track[$this->getIdentifier()]) === 0) {
                            //
                        } elseif (count($indexes = array_keys($track[$this->getIdentifier()], $this, true)) === 1) {
                            $index = $indexes[0];
                        }

                        foreach ($result as &$dataset) {
                            $filterValue = ($index !== null && is_array($dataset[$collection->getBaseField()])) ? $dataset[$collection->getBaseField()][$index] : $dataset[$collection->getBaseField()];

                            $vModel->addFilter($collection->getCollectionModelBaseRefField(), $filterValue);
                            $vResult = $vModel->search()->getResult();

                            // new method: deep dive to set data
                            $dive = &$dataset;
                            foreach ($structure as $key) {
                                $dive[$key] = $dive[$key] ?? [];
                                $dive = &$dive[$key];
                            }
                            $dive[$field] = $vResult;
                        }
                    }
                }
                // TODO: Handle collections?
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setVirtualFieldResult(bool $state): model
    {
        $this->virtualFieldResult = $state;
        return $this;
    }

    /**
     * [populateTrackFieldsRecursive description]
     * @param array  &$trackFields [description]
     * @return void [type]              [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function populateTrackFieldsRecursive(array &$trackFields): void
    {
        // Track this model
        if (count($trackFields) === 0) {
            $vModelFieldlist = $this->getCurrentAliasedFieldlist();
            foreach ($vModelFieldlist as $field) {
                $trackFields[$field][] = $this;
            }
        }

        foreach ($this->getNestedJoins() as $join) {
            // for field tracking
            // we have to make sure to only track
            // 'compatible' models:
            // - same DB/data technology
            // - same DB/data connection*
            // - not a forced virtual join
            // - ... etc
            //
            // * = TODO: to be fully implemented. Not sure if we're doing it right, atm.
            if ($this->compatibleJoin($join->model) && $join->model instanceof sql) {
                $vModelFieldlist = $join->model->getCurrentAliasedFieldlist();
                foreach ($vModelFieldlist as $field) {
                    //
                    // exclude virtual fields?
                    //
                    if ($join->model->getFieldtype(modelfield::getInstance($field)) === 'virtual') {
                        continue;
                    }

                    $trackFields[$field][] = $join->model;
                }

                // NOTE: compatibility already checked above
                $join->model->populateTrackFieldsRecursive($trackFields);
            }
        }
    }

    /**
     * [normalizeRecursivelyByFieldlist description]
     * @param array $result [description]
     * @return array         [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function normalizeRecursivelyByFieldlist(array $result): array
    {
        $fResult = [];

        //
        // normalize
        //
        foreach ($this->getNestedJoins() as $join) {
            if ($join->virtualField) {
                continue;
            }

            /**
             * FIXME @Kevin: Weil wegen Baum und sehr, sehr russisch
             * @var [type]
             */
            if ($join->model instanceof json) {
                continue;
            }

            $normalized = $join->model->normalizeRecursivelyByFieldlist($result);

            // // METHOD 1: merge manually, row by row
            foreach ($normalized as $index => $r) {
                // normalize using this model
                $fResult[$index] = array_merge(($fResult[$index] ?? []), $r);
            }

            // METHOD 2: recursive merge
            // NOTE: Actually, this doesn't work right.
            // It may split a model's result apart into two array elements in some cases.
            // $fResult = array_merge_recursive($fResult, $join->model->normalizeRecursivelyByFieldlist($result));
        }

        // CHANGED 2021-03-13: build static fieldlist for normalization
        // reduces calls to various array functions
        // AND: fixes hidden field handling for certain use cases
        $currentFieldlist = $this->getInternalIntersectFieldlist();

        //
        // Normalize using this model's fields
        //
        foreach ($result as $index => $r) {
            // normalize using this model
            // CHANGED 2019-05-24: additionally call $this->normalizeRow around normalizeByFieldlist,
            // otherwise we might run into issues, e.g.
            // - "structure"-type fields are not json_decode'd, if present on the root model
            // - ... other things?
            // NOTE: as of 2019-09-10 the normalization of structure fields has changed
            $fResult[$index] = array_merge(($fResult[$index] ?? []), $this->normalizeRow($this->normalizeByFieldlist($r, $currentFieldlist)));
        }

        return $fResult;
    }

    /**
     * returns the internal list of fields
     * to be expected in the output and used via array intersection
     * NOTE: the returned result array is flipped!
     * @return array [description]
     */
    protected function getInternalIntersectFieldlist(): array
    {
        $fields = $this->getFields();
        if (count($this->hiddenFields) > 0) {
            // remove hidden fields
            $diff = array_diff($fields, $this->hiddenFields);
            $fields = array_intersect($fields, $diff);
        }
        // VFR keys
        $vfrKeys = [];
        if ($this->virtualFieldResult) {
            foreach ($this->getNestedJoins() as $join) {
                if ($join->virtualField) {
                    $vfrKeys[] = $join->virtualField;
                }
            }
        }

        if (count($this->fieldlist) > 0) {
            return array_flip(array_merge($this->getFieldlistArray($this->fieldlist), $fields, $vfrKeys, array_keys($this->virtualFields)));
        } else {
            return array_flip(array_merge($fields, array_keys($this->virtualFields), $vfrKeys));
        }
    }

    /**
     * [normalizeByFieldlist description]
     * @param array $dataset [description]
     * @param array|null $fieldlist [optional, new: static fieldlist]
     * @return array          [description]
     */
    public function normalizeByFieldlist(array $dataset, ?array $fieldlist = null): array
    {
        if ($fieldlist) {
            // CHANGED 2021-04-13: use provided fieldlist, see above
            return array_intersect_key($dataset, $fieldlist);
        } elseif (count($this->fieldlist) > 0) {
            // return $dataset;
            return array_intersect_key($dataset, array_flip(array_merge($this->getFieldlistArray($this->fieldlist), $this->getFields(), array_keys($this->virtualFields))));
        } else {
            // return $dataset;
            return array_intersect_key($dataset, array_flip(array_merge($this->getFields(), array_keys($this->virtualFields))));
        }
    }

    /**
     * [saveLog description]
     * @param string $mode [description]
     * @param array $data [description]
     * @return void [type]       [description]
     */
    protected function saveLog(string $mode, array $data): void
    {
    }

    /**
     * Converts the array of fields into the field list for the query "value1, value2 "
     * @param array $fields
     * @return string
     */
    protected function getFieldlist(array $fields): string
    {
        $index = 0;
        $text = ' ';
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                if ($index > 0) {
                    $text .= ', ';
                }
                $text .= $field->field->get() . ' ';
                $index++;
            }
        }
        return $text;
    }
}
