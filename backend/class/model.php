<?php

namespace codename\core;

use codename\core\model\modelInterface;
use codename\core\model\plugin;
use codename\core\model\plugin\aggregate;
use codename\core\model\plugin\aggregate\aggregateInterface;
use codename\core\model\plugin\aggregatefilter;
use codename\core\model\plugin\calculatedfield;
use codename\core\model\plugin\calculatedfield\calculatedfieldInterface;
use codename\core\model\plugin\collection;
use codename\core\model\plugin\field;
use codename\core\model\plugin\filter;
use codename\core\model\plugin\filter\filterInterface;
use codename\core\model\plugin\fulltext\fulltextInterface;
use codename\core\model\plugin\group;
use codename\core\model\plugin\join;
use codename\core\model\plugin\join\dynamicJoinInterface;
use codename\core\model\plugin\join\executableJoinInterface;
use codename\core\model\plugin\managedFilterInterface;
use codename\core\model\plugin\order;
use codename\core\model\schemeless\dynamic;
use codename\core\model\virtualFieldResultInterface;
use codename\core\value\text\modelfield;
use codename\core\value\text\modelfield\virtual;
use DateTime;
use LogicException;
use ReflectionException;

use function array_key_exists;
use function count;
use function is_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function strlen;

/**
 * Storing data in different storage types (SQL, noSQL). Is one very important core of the framework
 * @package core
 * @since 2016-01-11
 * @todo mongoDB
 * @todo elasticSearch
 * @todo mySQL
 * @todo msSQL
 * @todo XML
 * @todo CSV
 */
abstract class model implements modelInterface
{
    /**
     * You want to set a field that is not present in the model
     * @var string
     */
    public const EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL';

    /**
     * You want to set a field of an entry, but there is no entry loaded, yet
     * @var string
     */
    public const EXCEPTION_FIELDSET_NOOBJECTLOADED = 'EXCEPTION_FIELDSET_NOOBJECTLOADED';

    /**
     * You want to get the content of a field but the desired field is not available
     * @var string
     */
    public const EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL';

    /**
     * You want to get the value of a field but there is no object loaded currently
     * @var string
     */
    public const EXCEPTION_FIELDGET_NOOBJECTLOADED = 'EXCEPTION_FIELDGET_NOOBJECTLOADED';

    /**
     * You want to delete an entry, but you did not load the entry, yet
     * @var string
     */
    public const EXCEPTION_ENTRYDELETE_NOOBJECTLOADED = 'EXCEPTION_ENTRYDELETE_NOOBJECTLOADED';

    /**
     * You want to save an entry, but you did not load it, yet.
     * @var string
     */
    public const EXCEPTION_ENTRYSAVE_NOOBJECTLOADED = 'EXCEPTION_ENTRYSAVE_NOOBJECTLOADED';

    /**
     * Entry load failed (wrong ID or inaccessible)
     * @var string
     */
    public const EXCEPTION_ENTRYLOAD_FAILED = 'EXCEPTION_ENTRYLOAD_FAILED';

    /**
     * You want to update an element, but it seems that the current element is empty
     * @var string
     */
    public const EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY = 'EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY';

    /**
     * You want to update an element there is no object available in the current resource
     * @var string
     */
    public const EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED = 'EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED';

    /**
     * You want to set the flag of an entry, but you did not load an entry
     * @var string
     */
    public const EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED = 'EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED';

    /**
     * The loaded entry does not contain flags
     * @var string
     */
    public const EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL';

    /**
     * You want to unset a flag but the element is empty
     * @var string
     */
    public const EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED = 'EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED';

    /**
     * You want to unset a flag but there are no flags in this model
     * @var string
     */
    public const EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL';

    /**
     * Exception thrown if an invalid flag value is provided
     * @var string
     */
    public const EXCEPTION_INVALID_FLAG_VALUE = 'EXCEPTION_INVALID_FLAG_VALUE';

    /**
     * You want to get a flag field value, but there are no flags in this model
     * @var string
     */
    public const EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL = 'EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL';


    /**
     * You want to add a default filter but the field was not found
     * @var string
     */
    public const EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND = 'EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND';

    /**
     * You want to add an order object but the field does not exist in the model
     * @var string
     */
    public const EXCEPTION_ADDORDER_FIELDNOTFOUND = 'EXCEPTION_ADDORDER_FIELDNOTFOUND';

    /**
     * The field you want to add to the response is not available in the model
     * @var string
     */
    public const EXCEPTION_ADDFIELD_FIELDNOTFOUND = 'EXCEPTION_ADDFIELD_FIELDNOTFOUND';

    /**
     * The field you want to add to the response is not available in the model
     * @var string
     */
    public const EXCEPTION_HIDEFIELD_FIELDNOTFOUND = 'EXCEPTION_HIDEFIELD_FIELDNOTFOUND';

    /**
     * You want to know the primary key, but it remains unset from the configuration
     * @var string
     */
    public const EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG = 'EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG';

    /**
     * You want to get the flag of an entry but the given flag was not found
     * @var string
     */
    public const EXCEPTION_GETFLAG_FLAGNOTFOUND = 'EXCEPTION_GETFLAG_FLAGNOTFOUND';

    /**
     * The model is missing a flag field.
     * @var string
     */
    public const EXCEPTION_ISFLAG_NOFLAGFIELD = 'EXCEPTION_ISFLAG_NOFLAGFIELD';

    /**
     * Incompatible models during auto combineModels
     * @var string
     */
    public const EXCEPTION_AUTOCOMBINEMODELS_UNJOINABLE_MODELS = "EXCEPTION_AUTOCOMBINEMODELS_UNJOINABLE_MODELS";

    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    public const DB_TYPE = null;
    /**
     * exception thrown when trying to add a nonexisting field to grouping parameters
     * @var string
     */
    public const EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST = "EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST";
    /**
     * [EXCEPTION_ADDFULLTEXTFIELD_NO_FIELDS_FOUND description]
     * @var string
     */
    public const EXCEPTION_ADDFULLTEXTFIELD_NO_FIELDS_FOUND = 'EXCEPTION_ADDFULLTEXTFIELD_NO_FIELDS_FOUND';
    /**
     * exception thrown on duplicate field existence (during addition of an aggregated field)
     * @var string
     */
    public const EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS = 'EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS';
    /**
     * exception thrown if we try to add a calculated field which already exists (either as db field or another calculated one)
     * @var string
     */
    public const EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS = "EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS";
    /**
     * Contains the configuration
     * @var null|config
     */
    public ?config $config = null;
    /**
     * determines query storing state
     * @var bool
     */
    public bool $saveLastQuery = false;
    /**
     * Set to true if the query shall be cached after finishing.
     * @var bool
     */
    protected bool $cache = false;
    /**
     * Contains the driver that is used to load the PDO class
     * @var null|string
     */
    protected ?string $driver = null;
    /**
     * array contains the result of the given query
     * @var null|array $result
     */
    protected ?array $result = null;
    /**
     * add an index for the function use index
     * @var string[]
     */
    protected array $useIndex = [];
    /**
     * Contains instances of the filters for the model request
     * @var filter[] $filter
     */
    protected array $filter = [];
    /**
     * Contains instances of the filters that will be used again after resetting the model
     * @var array $filter
     */
    protected array $defaultfilter = [];
    /**
     * Contains instances of aggregate filters for the model request
     * @var aggregatefilter[] $aggregateFilter
     */
    protected array $aggregateFilter = [];
    /**
     * Contains instances of default (reused) aggregate filters for the model request
     * @var aggregatefilter[] $defaultAggregateFilter
     */
    protected array $defaultAggregateFilter = [];
    /**
     * Contains an array of integer values for binary checks against the flag field
     * @var array $flagfilter
     **/
    protected array $flagfilter = [];
    /**
     * Like flagfilter, but retains its value through a reset
     * @var array $defaultflagfilter
     **/
    protected array $defaultflagfilter = [];
    /**
     * Contains the instances of the order directives for the model request
     * @var array $order
     */
    protected array $order = [];
    /**
     * Contains the list of fields that shall be returned
     * @var array
     */
    protected array $fieldlist = [];
    /**
     * Contains the list of fields to be hidden in result
     * @var array
     */
    protected array $hiddenFields = [];
    /**
     * Contains the instance of the limitation data for the model request
     * @var null|plugin\limit $limit
     */
    protected ?plugin\limit $limit = null;
    /**
     * Contains the instance of the offset data for the model request
     * @var null|plugin\offset
     */
    protected ?plugin\offset $offset = null;
    /**
     * Duplicate filtering state
     * @var bool
     */
    protected bool $filterDuplicates = false;
    /**
     * Contains the database connection
     * @var null|database
     */
    protected ?database $db = null;
    /**
     * Contains the application this model is originated in for file system operations
     * @var null|string
     */
    protected ?string $appname = null;
    /**
     * Contains the delimiter for strings
     * @var string $delimiter
     */
    protected string $delimiter = "'";
    /**
     * Contains the errorstack for this instance
     * @var null|errorstack
     */
    protected ?errorstack $errorstack = null;
    /**
     * Contains the datacontainer object
     * @var null|datacontainer
     */
    protected ?datacontainer $data = null;
    /**
     * [protected description]
     * @var collection[]
     */
    protected array $collectionPlugins = [];
    /**
     * state of force_virtual_join feature
     * @var bool
     */
    protected bool $forceVirtualJoin = false;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $recursive = false;
    /**
     * [protected description]
     * @var null|value\text\modelfield
     */
    protected ?value\text\modelfield $recursiveSelfReferenceField = null;
    /**
     * [protected description]
     * @var null|value\text\modelfield
     */
    protected ?value\text\modelfield $recursiveAnchorField = null;
    /**
     * [protected description]
     * @var filter[]
     */
    protected array $recursiveAnchorConditions = [];
    /**
     * contains configured join plugin instances for nested models
     * @var join[]
     */
    protected array $nestedModels = [];
    /**
     * [protected description]
     * @var null|model\servicing\sql
     */
    protected ?model\servicing\sql $servicingInstance = null;
    /**
     * model data passed during initialization
     * @var null|config
     */
    protected ?config $modeldata = null;
    /**
     * Provides an additional collection of filter arrays
     * to be used in queries.
     * like [0] => {
     *          operator => 'AND'
     *          filters => { filter1, filter2 }
     * }
     * ...
     * which are chained as groups with the default operator (AND - or other, if defined)
     * and chained internally as defined via joinMethod
     * @var array
     */
    protected array $filterCollections = [];
    /**
     * Contains instances of the filters that will be used again after resetting the model
     * @var array
     */
    protected array $defaultfilterCollections = [];
    /**
     * virtual field functions
     * @var callable[]
     */
    protected array $virtualFields = [];
    /**
     * groupBy fields
     * @var group[]
     */
    protected array $group = [];
    /**
     * internal in-mem caching of fieldtypes
     * @var array
     */
    protected array $cachedFieldtype = [];
    /**
     * primarykey cache field
     * @var null|string
     */
    protected ?string $primarykey = null;
    /**
     * internal caching variable containing the list of fields in the model
     * @var null|array
     */
    protected ?array $normalizeDataFieldCache = null;
    /**
     * contains the last query performed with this model instance
     * @var string
     */
    protected string $lastQuery = '';
    /**
     * Temporary model field cache during normalizeResult / normalizeRow
     * This is being reset each time normalizeResult is going to call normalizeRow
     * @var modelfield[]
     */
    protected array $normalizeModelFieldCache = [];
    /**
     * Temporary model field type cache during normalizeResult / normalizeRow
     * This is being reset each time normalizeResult is going to call normalizeRow
     * @var modelfield[]
     */
    protected array $normalizeModelFieldTypeCache = [];
    /**
     * [protected description]
     * @var bool[]
     */
    protected array $normalizeModelFieldTypeStructureCache = [];
    /**
     * [protected description]
     * @var bool[]
     */
    protected array $normalizeModelFieldTypeVirtualCache = [];
    /**
     * internal variable containing field types for a given field
     * to improve performance of ::importField
     * @var array [type]
     */
    protected array $importFieldTypeCache = [];
    /**
     * @var array
     */
    protected array $fieldTypeCache = [];

    /**
     * Creates an instance
     * @param array $modeldata
     * @return model
     * @todo refactor the constructor for no method args
     */
    public function __construct(array $modeldata = [])
    {
        $this->errorstack = new errorstack('VALIDATION');
        $this->modeldata = new config($modeldata);
        return $this;
    }

    /**
     * returns the config object
     * @return config [description]
     */
    public function getConfig(): config
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(): int
    {
        //
        // NOTE: this has to be implemented per DB technology
        //
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * [getNestedCollections description]
     * @return collection[] [description]
     */
    public function getNestedCollections(): array
    {
        return $this->collectionPlugins;
    }

    /**
     * I will validate the currently loaded dataset of the current model and return the array of errors that might have occurred
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function entryValidate(): array
    {
        return $this->validate($this->data->getData())->getErrors();
    }

    /**
     * outputs a singular and final flag field value
     * based on a given starting point - which may also be 0 (no flag)
     * as a combination of several flags given (with states)
     * this DOES NOT change existing flags, unless they're explicitly specified in another state
     */

    /**
     * Returns the errors of the errorstack in this instance
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errorstack->getErrors();
    }

    /**
     * Validates the given data after normalizing it.
     * @param array $data
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @todo required seems to have some bugs
     * @todo bring back to life the UNIQUE constraint checker
     * @todo move the UNIQUE constraint checks to a separate method
     */
    public function validate(array $data): model
    {
        //
        // CHANGED 2020-07-29 reset the current errorstack just right before validation
        //
        $this->errorstack->reset();

        foreach ($this->config->get('field') as $field) {
            if (in_array($field, [$this->getPrimaryKey(), $this->getIdentifier() . "_modified", $this->getIdentifier() . "_created"])) {
                continue;
            }
            if (!array_key_exists($field, $data) || is_null($data[$field]) || (is_string($data[$field]) && strlen($data[$field]) == 0)) {
                if (is_array($this->config->get('required')) && in_array($field, $this->config->get("required"))) {
                    $this->errorstack->addError($field, 'FIELD_IS_REQUIRED');
                }
                continue;
            }

            if ($this->config->exists('children') && $this->config->exists('children>' . $field)) {
                // validate child using child/nested model
                $childConfig = $this->config->get('children>' . $field);

                if ($childConfig['type'] === 'foreign') {
                    //
                    // Normal Foreign-Key based child (1:1)
                    //
                    $foreignConfig = $this->config->get('foreign>' . $childConfig['field']);

                    // get the join plugin valid for the child reference field
                    $res = $this->getNestedJoins($foreignConfig['model'], $childConfig['field']);

                    if (count($res) === 1) {
                        $join = $res[0]; // reset($res);
                        $join->model->validate($data[$field]);
                        if (count($errors = $join->model->getErrors()) > 0) {
                            $this->errorstack->addError($field, 'FIELD_INVALID', $errors);
                        }
                    } else {
                        continue;
                    }
                } elseif ($childConfig['type'] === 'collection') {
                    //
                    // Collections in a virtual field
                    //

                    // TODO: get the corresponding model
                    // we might introduce a new "addCollectionModel" method or so

                    if (isset($this->collectionPlugins[$field])) {
                        if (is_array($data[$field])) {
                            foreach ($data[$field] as $collectionItem) {
                                $this->collectionPlugins[$field]->collectionModel->validate($collectionItem);
                                if (count($errors = $this->collectionPlugins[$field]->collectionModel->getErrors()) > 0) {
                                    $this->errorstack->addError($field, 'FIELD_INVALID', $errors);
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                }
            }

            if (count($errors = app::getValidator($this->getFieldtype($this->getModelfieldInstance($field)))->reset()->validate($data[$field])) > 0) {
                $this->errorstack->addError($field, 'FIELD_INVALID', $errors);
            }
        }

        // model validator
        if ($this->config->exists('validators')) {
            $validators = $this->config->get('validators');
            foreach ($validators as $validator) {
                // NOTE: reset validator needed, as app::getValidator() caches the validator instance,
                // including the current errorstack
                if (count($errors = app::getValidator($validator)->reset()->validate($data)) > 0) {
                    //
                    // NOTE/CHANGED 2020-02-18
                    // split errors into field-related and others
                    // to improve validation handling
                    //
                    $dataErrors = [];
                    $fieldErrors = [];
                    foreach ($errors as $error) {
                        if (in_array($error['__IDENTIFIER'], $this->getFields())) {
                            $fieldErrors[] = $error;
                        } else {
                            $dataErrors[] = $error;
                        }
                    }
                    if (count($dataErrors) > 0) {
                        $this->errorstack->addError('DATA', 'INVALID', $dataErrors);
                    }
                    if (count($fieldErrors) > 0) {
                        $this->errorstack->addErrors($fieldErrors);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * resets all the parameters of the instance for another query
     * @return void
     */
    public function reset(): void
    {
        $this->cache = false;
        // $this->fieldlist = [];
        // $this->hiddenFields = [];
        $this->filter = $this->defaultfilter;
        $this->aggregateFilter = $this->defaultAggregateFilter;
        $this->flagfilter = $this->defaultflagfilter;
        $this->filterCollections = $this->defaultfilterCollections;
        $this->limit = null;
        $this->offset = null;
        $this->filterDuplicates = false;
        $this->order = [];
        $this->errorstack->reset();
        foreach ($this->nestedModels as $nest) {
            $nest->model->reset();
        }
        // TODO: reset collection models?
    }

    /**
     * Returns the primary key that was configured in the model's JSON config
     * @return string
     * @throws exception
     */
    public function getPrimaryKey(): string
    {
        if ($this->primarykey === null) {
            if (!$this->config->exists("primary")) {
                throw new exception(self::EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG, exception::$ERRORLEVEL_FATAL, $this->config->get());
            }
            $this->primarykey = $this->config->get('primary')[0];
        }
        return $this->primarykey;
    }

    /**
     * Gets the current model identifier (name)
     * @return string
     */
    abstract public function getIdentifier(): string;

    /**
     * [getNestedJoins description]
     * @param string|null $model name of a model to look for
     * @param string|null $modelField name of a field the model is joined upon
     * @return join[]   [array of joins, may be empty]
     */
    public function getNestedJoins(string $model = null, string $modelField = null): array
    {
        if ($model || $modelField) {
            return array_values(
                array_filter($this->getNestedJoins(), function (join $join) use ($model, $modelField) {
                    return ($model === null || $join->model->getIdentifier() === $model) && ($modelField === null || $join->modelField === $modelField);
                })
            );
        } else {
            return $this->nestedModels;
        }
    }

    /**
     * Returns the datatype of the given field
     * @param modelfield $field
     * @return string|null
     */
    public function getFieldtype(modelfield $field): ?string
    {
        $specifier = $field->get();
        if (!isset($this->cachedFieldtype[$specifier])) {
            if (($fieldtype = $this->config->get("datatype>" . $specifier))) {
                // field in this model
                $this->cachedFieldtype[$specifier] = $fieldtype;
            } else {
                // check nested model configs
                foreach ($this->nestedModels as $joinPlugin) {
                    $fieldtype = $joinPlugin->model->getFieldtype($field);
                    if ($fieldtype !== null) {
                        $this->cachedFieldtype[$specifier] = $fieldtype;
                        return $fieldtype;
                    }
                }
                $this->cachedFieldtype[$specifier] = null;
            }
        }
        return $this->cachedFieldtype[$specifier];
    }

    /**
     * [getModelfieldInstance description]
     * @param string $field [description]
     * @return modelfield        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getModelfieldInstance(string $field): modelfield
    {
        return modelfield::getInstance($field);
    }

    /**
     * Returns array of fields that exist in the model
     * @return array
     */
    public function getFields(): array
    {
        return $this->config->get('field');
    }

    /**
     * [getData description]
     * @return array [description]
     */
    public function getData(): array
    {
        return $this->data->getData();
    }

    /**
     * I will delete the previously loaded entry.
     * @return model
     * @throws exception
     */
    public function entryDelete(): model
    {
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_ENTRYDELETE_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL);
        }
        $this->delete($this->data->getData($this->getPrimaryKey()));
        return $this;
    }

    /**
     * [addCollectionModel description]
     * @param model $model [description]
     * @param string|null $modelField [description]
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function addCollectionModel(model $model, string $modelField = null): model
    {
        if ($this->config->exists('collection')) {
            $collectionConfig = null;

            //
            // try to determine modelfield by the best-matching collection
            //
            if (!$modelField) {
                if ($this->config->exists('collection')) {
                    foreach ($this->config->get('collection') as $collectionFieldName => $config) {
                        if ($config['model'] === $model->getIdentifier()) {
                            $modelField = $collectionFieldName;
                            $collectionConfig = $config;
                        }
                    }
                }
            }

            //
            // Still no modelfield
            //
            if (!$modelField) {
                throw new exception('EXCEPTION_UNKNOWN_COLLECTION_MODEL', exception::$ERRORLEVEL_ERROR, [$this->getIdentifier(), $model->getIdentifier()]);
            }

            //
            // Case where we haven't retrieved the collection config yet
            //
            if (!$collectionConfig) {
                $collectionConfig = $this->config->get('collection>' . $modelField);
            }

            //
            // Still no collection config
            //
            if (!$collectionConfig) {
                throw new exception('EXCEPTION_NO_COLLECTION_CONFIG', exception::$ERRORLEVEL_ERROR, $modelField);
            }

            if ($collectionConfig['model'] != $model->getIdentifier()) {
                throw new exception('EXCEPTION_MODEL_ADDCOLLECTIONMODEL_INCOMPATIBLE', exception::$ERRORLEVEL_ERROR, [$collectionConfig['model'], $model->getIdentifier()]);
            }

            $modelFieldInstance = $this->getModelfieldInstance($modelField);

            // Finally, add model
            $this->collectionPlugins[$modelFieldInstance->get()] = new collection(
                $modelFieldInstance,
                $this,
                $model
            );
        } else {
            throw new exception('EXCEPTION_NO_COLLECTION_KEY', exception::$ERRORLEVEL_ERROR, $this->getIdentifier());
        }

        return $this;
    }

    /**
     * adds a model using custom parameters
     * and optionally using custom extra conditions
     *
     * this can be used to join models that have no explicit foreign key reference to each other
     *
     * @param model $model [description]
     * @param string $type [description]
     * @param string|null $modelField [description]
     * @param string|null $referenceField [description]
     * @param array $conditions
     * @return model                 [description]
     */
    public function addCustomJoin(model $model, string $type = join::TYPE_LEFT, ?string $modelField = null, ?string $referenceField = null, array $conditions = []): model
    {
        $thisKey = $modelField;
        $joinKey = $referenceField;

        // fallback to bare model joining
        if ($model instanceof dynamic || $this instanceof dynamic) {
            $pluginDriver = 'dynamic';
        } else {
            $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
        }

        $class = '\\codename\\core\\model\\plugin\\join\\' . $pluginDriver;
        $this->nestedModels[] = new $class($model, $type, $thisKey, $joinKey, $conditions);
        return $this;
    }

    /**
     * determines if the model is join able
     * in the same run (e.g. DB compatibility and stuff)
     * @param model $model [the model to check direct join compatibility with]
     * @return bool
     */
    protected function compatibleJoin(model $model): bool
    {
        //
        // NOTE/CHANGED 2020-07-21: Feature 'force_virtual_join' is checked right here
        // to allow virtually joining a table via already available features of the framework.
        // This overcomes the problem of join count limitations
        // While preserving ORM capabilities
        //
        // If $model has the force_virtual_join feature enabled,
        // this method will return false, no matter if its mysql==mysql or else.
        //
        return $this->getType() == $model->getType() && !$model->getForceVirtualJoin();
    }

    /**
     * Returns the driver that shall be used for the model
     * @return string
     */
    protected function getType(): string
    {
        return static::DB_TYPE;
    }

    /**
     * Gets the current state of the force_virtual_join feature
     * @return bool
     */
    public function getForceVirtualJoin(): bool
    {
        return $this->forceVirtualJoin;
    }

    /**
     * Sets the force_virtual_join feature state
     * This enables the model to be joined virtually
     * to avoid join limits of various RDBMS
     * @param bool $state
     * @return model
     */
    public function setForceVirtualJoin(bool $state): static
    {
        $this->forceVirtualJoin = $state;
        return $this;
    }

    /**
     * [addRecursiveModel description]
     * @param model $model [model instance to recurse]
     * @param string $selfReferenceField [field used for self-referencing]
     * @param string $anchorField [field used as anchor point]
     * @param array $anchorConditions [additional anchor conditions - e.g. the starting point]
     * @param string $type [type of join]
     * @param string|null $modelField [description]
     * @param string|null $referenceField [description]
     * @param array $conditions [description]
     * @return model                     [description]
     * @throws exception
     */
    public function addRecursiveModel(model $model, string $selfReferenceField, string $anchorField, array $anchorConditions, string $type = join::TYPE_LEFT, ?string $modelField = null, ?string $referenceField = null, array $conditions = []): model
    {
        $thisKey = $modelField;
        $joinKey = $referenceField;

        // TODO: auto-determine modelField and referenceField / thisKey and joinKey

        if (
            (
                !$model->config->get('foreign>' . $selfReferenceField . '>model') == $model->getIdentifier() ||
                !$model->config->get('foreign>' . $selfReferenceField . '>key') == $model->getPrimaryKey()
            ) && (
                !$model->config->get('foreign>' . $anchorField . '>model') == $model->getIdentifier() ||
                !$model->config->get('foreign>' . $anchorField . '>key') == $model->getPrimaryKey()
            )
        ) {
            throw new exception('INVALID_RECURSIVE_MODEL_JOIN', exception::$ERRORLEVEL_ERROR);
        }

        // fallback to bare model joining
        if ($model instanceof dynamic || $this instanceof dynamic) {
            $pluginDriver = 'dynamic';
        } else {
            $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
        }

        $class = '\\codename\\core\\model\\plugin\\join\\recursive\\' . $pluginDriver;
        $this->nestedModels[] = new $class($model, $selfReferenceField, $anchorField, $anchorConditions, $type, $thisKey, $joinKey, $conditions);
        return $this;
    }

    /**
     * [setRecursive description]
     * @param string $selfReferenceField [description]
     * @param string $anchorField [description]
     * @param array $anchorConditions [description]
     * @return model                     [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function setRecursive(string $selfReferenceField, string $anchorField, array $anchorConditions): model
    {
        if ($this->recursive) {
            // kill, already active?
            throw new exception('EXCEPTION_MODEL_SETRECURSIVE_ALREADY_ENABLED', exception::$ERRORLEVEL_ERROR);
        }

        $this->recursive = true;

        if (
            (
                !$this->config->get('foreign>' . $selfReferenceField . '>model') == $this->getIdentifier() ||
                !$this->config->get('foreign>' . $selfReferenceField . '>key') == $this->getPrimaryKey()
            ) && (
                !$this->config->get('foreign>' . $anchorField . '>model') == $this->getIdentifier() ||
                !$this->config->get('foreign>' . $anchorField . '>key') == $this->getPrimaryKey()
            )
        ) {
            throw new exception('INVALID_RECURSIVE_MODEL_CONFIG', exception::$ERRORLEVEL_ERROR);
        }

        $this->recursiveSelfReferenceField = $this->getModelfieldInstance($selfReferenceField);
        $this->recursiveAnchorField = $this->getModelfieldInstance($anchorField);

        foreach ($anchorConditions as $cond) {
            if ($cond instanceof filter) {
                $this->recursiveAnchorConditions[] = $cond;
            } else {
                $this->recursiveAnchorConditions[] = $this->createFilterPluginInstance($cond);
            }
        }

        return $this;
    }

    /**
     * [createFilterPluginInstance description]
     * @param array $data [description]
     * @return filter        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function createFilterPluginInstance(array $data): filter
    {
        $field = $data['field'];
        $value = $data['value'];
        $operator = $data['operator'];
        $conjunction = $data['conjunction'] ?? null;
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if (is_array($value)) {
            if (count($value) === 0) {
                throw new exception('EXCEPTION_MODEL_CREATEFILTERPLUGININSTANCE_INVALID_VALUE', exception::$ERRORLEVEL_ERROR);
            }
            return new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            return new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
        }
    }

    /**
     * [delimitImproved description]
     * @param string $field [description]
     * @param null $value
     * @return mixed [type]        [description]
     */
    protected function delimitImproved(string $field, $value = null): mixed
    {
        $fieldtype = $this->fieldTypeCache[$field] ?? $this->fieldTypeCache[$field] = $this->getFieldtypeImproved($field);

        // CHANGED 2020-12-30 removed \is_string($value) && \strlen($value) == 0
        // Which converted '' to NULL - which is simply wrong.
        if ($value === null) {
            return null;
        }

        if ($fieldtype == 'number') {
            if (is_numeric($value)) {
                return $value;
            }
            if (strlen($value) == 0) {
                return null;
            }
            return $value;
        }
        if ($fieldtype == 'number_natural') {
            if (is_int($value)) {
                return $value;
            }
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
            return (int)$value;
        }
        if ($fieldtype == 'boolean') {
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
            if ($value) {
                return true;
            }
            return false;
        }
        if (str_starts_with($fieldtype, 'text')) {
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
        }
        return $value;
    }

    /**
     * [getFieldtypeImproved description]
     * @param string $specifier [description]
     * @return string|null
     */
    public function getFieldtypeImproved(string $specifier): ?string
    {
        if (!isset($this->cachedFieldtype[$specifier])) {
            // fieldtype not in current model config
            if (($fieldtype = $this->config->get("datatype>" . $specifier))) {
                // field in this model
                $this->cachedFieldtype[$specifier] = $fieldtype;
            } else {
                // check nested model configs
                foreach ($this->nestedModels as $joinPlugin) {
                    $fieldtype = $joinPlugin->model->getFieldtypeImproved($specifier);
                    if ($fieldtype !== null) {
                        $this->cachedFieldtype[$specifier] = $fieldtype;
                        return $fieldtype;
                    }
                }

                $this->cachedFieldtype[$specifier] = null;
            }
        }
        return $this->cachedFieldtype[$specifier];
    }

    /**
     * I load an entry of the given model identified by the $primarykey to the current instance.
     * @param string $primaryKey
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function entryLoad(string $primaryKey): model
    {
        $entry = $this->loadByUnique($this->getPrimaryKey(), $primaryKey);
        if (empty($entry)) {
            throw new exception(self::EXCEPTION_ENTRYLOAD_FAILED, exception::$ERRORLEVEL_FATAL);
        }
        $this->entryMake($entry);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @param string $field
     * @param string $value
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function loadByUnique(string $field, string $value): array
    {
        $data = $this
          ->addFilter($field, $value)
          ->setLimit(1)
          ->search()->getResult();
        if (count($data) == 0) {
            return [];
        }
        return $data[0];
    }

    /**
     * {@inheritDoc}
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function getResult(): array
    {
        $result = $this->result;

        if ($result === null) {
            $this->result = $this->internalGetResult();
            $result = $this->result;
        }

        // execute any bare joins, if set
        $result = $this->performBareJoin($result);

        $result = $this->normalizeResult($result);
        $this->data = new datacontainer($result);
        return $this->data->getData();
    }

    /**
     * internal getResult
     * @return array
     */
    abstract protected function internalGetResult(): array;

    /**
     * perform a shim / bare metal join
     * @param array $result [the resultset]
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function performBareJoin(array $result): array
    {
        if (count($this->getNestedJoins()) == 0) {
            return $result;
        }

        //
        // Loop through Joins
        //
        foreach ($this->getNestedJoins() as $join) {
            $nest = $join->model;

            $vKey = null;
            if ($this instanceof virtualFieldResultInterface && $this->virtualFieldResult) {
                $vKey = $join->virtualField;
            }

            // virtual field?
            if ($vKey && !$nest->getForceVirtualJoin()) {
                //
                // NOTE/CHANGED 2020-09-15 Forced virtual joins
                // require us to skip performBareJoin at this point in general
                // (for both vkey and non-vkey joins)
                //

                //
                // Skip recursive performBareJoin
                // if we have none coming up next
                //
                if (count($nest->getNestedJoins()) == 0) {
                    continue;
                }

                // make sure vKey is in current fieldlist...
                // this is for  situations
                // where
                // - virtual field result enabled
                // - vfield config present
                // - respective model joined
                // - another (bare-joined) model relying on a field
                // - but field(s) hidden, e.g. by hideAllFields
                $ifl = $this->getInternalIntersectFieldlist();

                if (!array_key_exists($vKey, $ifl)) {
                    throw new exception('EXCEPTION_MODEL_PERFORMBAREJOIN_MISSING_VKEY', exception::$ERRORLEVEL_ERROR, [
                      'model' => $this->getIdentifier(),
                      'vKey' => $vKey,
                    ]);
                }

                //
                // Unwind resultset
                // [ item, item, item ] -> [ item[key], item[key], item[key] ]
                //
                $tResult = array_map(function ($r) use ($vKey) {
                    return $r[$vKey];
                }, $result);

                //
                // Recursively check for bareJoin-able models
                // with a subset of the current result
                //
                $tResult = $nest->performBareJoin($tResult);

                //
                // Re-wind resultset
                // [ item[key], item[key], item[key] ] -> merge into [ item, item, item ]
                //
                foreach ($result as $index => &$r) {
                    $r[$vKey] = array_merge($r[$vKey], $tResult[$index]);
                }
            } elseif (!$nest->getForceVirtualJoin()) {
                //
                // NOTE/CHANGED 2020-09-15 Forced virtual joins
                // require us to skip performBareJoin at this point in general
                // (for both vkey and non-vkey joins)
                //
                $result = $nest->performBareJoin($result);
            }

            //
            // check if model is joining compatible
            // we explicitly join incompatible models using a bare-data here!
            //
            if (!$this->compatibleJoin($nest) && ($join instanceof executableJoinInterface)) {
                $subresult = $nest->search()->getResult();

                if ($vKey) {
                    //
                    // Unwind resultset
                    // [ item, item, item ] -> [ item[key], item[key], item[key] ]
                    //
                    $tResult = array_map(function ($r) use ($vKey) {
                        return $r[$vKey];
                    }, $result);

                    //
                    // Recursively perform the
                    // with a subset of the current result
                    //
                    $tResult = $join->join($tResult, $subresult);

                    //
                    // Re-wind resultset
                    // [ item[key], item[key], item[key] ] -> merge into [ item, item, item ]
                    //
                    foreach ($result as $index => &$r) {
                        $r[$vKey] = array_merge($tResult[$index]);
                    }
                } else {
                    $result = $join->join($result, $subresult);
                }
            } elseif (!$this->compatibleJoin($nest) && ($join instanceof dynamicJoinInterface)) {
                //
                // CHANGED 2020-07-22 vkey handling inside dynamic joins
                // Special join handling
                // using dynamic join method
                // vKey is specified either way (but may be null)
                // so the join module may handle the virtual field result
                //
                $result = $join->dynamicJoin($result, [
                  'vkey' => $vKey,
                ]);
            }
        }
        return $result;
    }

    /**
     * Normalizes a result. Nests normalizeRow when more than one single row is in the result.
     * @param array $result
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function normalizeResult(array $result): array
    {
        if (count($result) == 0) {
            return [];
        }

        // Normalize single row
        if (count($result) == 1) {
            $result = reset($result);
            return [$this->normalizeRow($result)];
        }

        // Normalize each row
        foreach ($result as $key => $value) {
            $result[$key] = $this->normalizeRow($value);
        }
        return $result;
    }

    /**
     * Normalizes a single row of a dataset
     * @param array $dataset
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function normalizeRow(array $dataset): array
    {
        if (count($dataset) == 1 && isset($dataset[0])) {
            $dataset = $dataset[0];
        }

        foreach ($dataset as $field => $thisRow) {
            // Performance optimization (and fix):
            // Check for (key == null) first, as it is faster than is_string
            // NOTE: checking for !is_string commented-out
            // we need to check - at least for booleans (DB provides 0 and 1 instead of true/false)
            // if($dataset[$field] === null || !is_string($dataset[$field])) {continue;}
            if ($thisRow === null) {
                continue;
            }

            // special case: we need boolean normalization (0 / 1)
            // but otherwise, just skip
            if (
                (isset($this->normalizeModelFieldTypeCache[$field]) && ($this->normalizeModelFieldTypeCache[$field] !== 'boolean'))
                && !is_string($thisRow)
            ) {
                continue;
            }

            // determine virtuality status of the field
            if (!isset($this->normalizeModelFieldTypeVirtualCache[$field])) {
                $tVirtualModelField = $this->getModelfieldVirtualInstance($field);
                $this->normalizeModelFieldTypeCache[$field] = $this->getFieldtype($tVirtualModelField);
                $this->normalizeModelFieldTypeVirtualCache[$field] = $this->normalizeModelFieldTypeCache[$field] === 'virtual';
            }

            ///
            /// Fixing a bad performance issue
            /// using result-specific model field caching
            /// as they're re-constructed EVERY call!
            ///
            if (!isset($this->normalizeModelFieldCache[$field])) {
                if ($this->normalizeModelFieldTypeVirtualCache[$field]) {
                    $this->normalizeModelFieldCache[$field] = $this->getModelfieldVirtualInstance($field);
                } else {
                    $this->normalizeModelFieldCache[$field] = $this->getModelfieldInstance($field);
                }
            }

            if (!isset($this->normalizeModelFieldTypeCache[$field])) {
                $this->normalizeModelFieldTypeCache[$field] = $this->getFieldtype($this->normalizeModelFieldCache[$field]);
            }

            //
            // HACK: only normalize boolean fields
            //
            if ($this->normalizeModelFieldTypeCache[$field] === 'boolean') {
                $dataset[$field] = $this->importField($this->normalizeModelFieldCache[$field], $thisRow);
                continue;
            }

            if (!isset($this->normalizeModelFieldTypeStructureCache[$field])) {
                $this->normalizeModelFieldTypeStructureCache[$field] = str_contains($this->normalizeModelFieldTypeCache[$field], 'structu');
            }

            if ($this->normalizeModelFieldTypeStructureCache[$field] && !is_array($thisRow)) {
                $dataset[$field] = $thisRow == null ? null : app::object2array(json_decode($thisRow, false)/*, 512, JSON_UNESCAPED_UNICODE)*/);
            }
        }
        return $dataset;
    }

    /**
     * [getModelfieldVirtualInstance description]
     * @param string $field [description]
     * @return modelfield        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getModelfieldVirtualInstance(string $field): modelfield
    {
        return virtual::getInstance($field);
    }

    /**
     * Converts the given field, and it's value from a human-readable format into a storage format
     * @param modelfield $field
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    protected function importField(modelfield $field, mixed $value = null): mixed
    {
        $fieldType = $this->importFieldTypeCache[$field->get()] ?? $this->importFieldTypeCache[$field->get()] = $this->getFieldtype($field);
        switch ($fieldType) {
            case 'number_natural':
                if (is_string($value) && strlen($value) === 0) {
                    return null;
                }
                break;
            case 'boolean':
                // allow null booleans
                // may be needed for conditional unique keys
                if (is_null($value)) {
                    return $value;
                }
                // pure boolean
                if (is_bool($value)) {
                    return $value;
                }
                // int: 0 or 1
                if (is_int($value)) {
                    if ($value !== 1 && $value !== 0) {
                        throw new exception('EXCEPTION_MODEL_IMPORTFIELD_BOOLEAN_INVALID', exception::$ERRORLEVEL_ERROR, [
                          'field' => $field->get(),
                          'value' => $value,
                        ]);
                    }
                    return $value === 1;
                }
                // string boolean
                if (is_string($value)) {
                    // fallback, empty string
                    if (strlen($value) === 0) {
                        return null;
                    }
                    if ($value === '1') {
                        return true;
                    } elseif ($value === '0') {
                        return false;
                    } elseif ($value === 'true') {
                        return true;
                    } elseif ($value === 'false') {
                        return false;
                    }
                }
                // fallback
                return false;
            case 'text_date':
                if (is_null($value)) {
                    return $value;
                }
                // automatically convert input value
                return (new DateTime($value))->format('Y-m-d');
        }
        return $value;
    }

    /**
     *
     * {@inheritDoc}
     * @see model_interface::setLimit
     */
    public function setLimit(int $limit): model
    {
        $class = '\\codename\\core\\model\\plugin\\limit\\' . $this->getType();
        $this->limit = new $class($limit);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $field
     * @param mixed|null $value
     * @param string $operator
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addFilter, $value, $operator)
     */
    public function addFilter(string $field, mixed $value = null, string $operator = '=', string $conjunction = null): model
    {
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if (is_array($value)) {
            if (count($value) === 0) {
                trigger_error('Empty array filter values have no effect on resultset');
                return $this;
            }
            $this->filter[] = new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            $this->filter[] = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
        }
        return $this;
    }

    /**
     * I am capable of creating a new entry for the current model by the given array $data.
     * @param array $data
     * @return model
     */
    public function entryMake(array $data = []): model
    {
        $this->data = new datacontainer($data);
        return $this;
    }

    /**
     * I save the currently loaded entry to the model storage
     * @return model
     * @throws exception
     */
    public function entrySave(): model
    {
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_ENTRYSAVE_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL);
        }
        $this->saveWithChildren($this->data->getData());
        return $this;
    }

    /**
     * I will overwrite the fields of my model using the $data array
     * @param array $data
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function entryUpdate(array $data): model
    {
        if (count($data) == 0) {
            throw new exception(self::EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY, exception::$ERRORLEVEL_FATAL, null);
        }
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL, null);
        }
        foreach ($this->getFields() as $field) {
            if (array_key_exists($field, $data)) {
                $this->fieldSet($this->getModelfieldInstance($field), $data[$field]);
            }
        }
        return $this;
    }

    /**
     * I will set the given $field's value to $value of the previously loaded dataset / entry.
     * @param modelfield $field
     * @param mixed $value
     * @return model
     * @throws exception
     */
    public function fieldSet(modelfield $field, mixed $value): model
    {
        if (!$this->fieldExists($field)) {
            throw new exception(self::EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL, exception::$ERRORLEVEL_FATAL, $field);
        }
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_FIELDSET_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL);
        }
        $this->data->setData($field->get(), $value);
        return $this;
    }

    /**
     * Returns true if the given $field exists in this model's configuration
     * @param modelfield $field
     * @return bool
     */
    protected function fieldExists(modelfield $field): bool
    {
        if ($field->getTable() != null) {
            if ($field->getTable() == ($this->table ?? null)) {
                return in_array($field->get(), $this->getFields());
            } else {
                foreach ($this->getNestedJoins() as $join) {
                    if ($join->model->fieldExists($field)) {
                        return true;
                    }
                }
            }
        }
        return in_array($field->get(), $this->getFields());
    }

    /**
     * I set a flag (identified by the integer $flagval) to TRUE.
     * @param int $flagval
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function entrySetFlag(int $flagval): model
    {
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL, null);
        }
        if (!$this->config->exists('flag')) {
            throw new exception(self::EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL, exception::$ERRORLEVEL_FATAL, null);
        }
        if ($flagval < 0) {
            // Only allow >= 0
            throw new exception(self::EXCEPTION_INVALID_FLAG_VALUE, exception::$ERRORLEVEL_ERROR, $flagval);
        }

        $flag = $this->fieldGet($this->getModelfieldInstance($this->table . '_flag'));
        $flag |= $flagval;
        $this->fieldSet($this->getModelfieldInstance($this->table . '_flag'), $flag);
        return $this;
    }

    /**
     * I will return the given $field's value of the previously loaded dataset.
     * @param modelfield $field
     * @return mixed
     * @throws exception
     */
    public function fieldGet(modelfield $field): mixed
    {
        if (!$this->fieldExists($field)) {
            throw new exception(self::EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL, exception::$ERRORLEVEL_FATAL, $field);
        }
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_FIELDGET_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL);
        }
        return $this->data->getData($field->get());
    }

    /**
     * I set a flag (identified by the integer $flagval) to FALSE.
     * @param int $flagval
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function entryUnsetFlag(int $flagval): model
    {
        if ($this->data === null || empty($this->data->getData())) {
            throw new exception(self::EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED, exception::$ERRORLEVEL_FATAL, null);
        }
        if (!$this->config->exists('flag')) {
            throw new exception(self::EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL, exception::$ERRORLEVEL_FATAL, null);
        }
        if ($flagval < 0) {
            // Only allow >= 0
            throw new exception(self::EXCEPTION_INVALID_FLAG_VALUE, exception::$ERRORLEVEL_ERROR, $flagval);
        }
        $flag = $this->fieldGet($this->getModelfieldInstance($this->table . '_flag'));
        $flag &= ~$flagval;
        $this->fieldSet($this->getModelfieldInstance($this->table . '_flag'), $flag);
        return $this;
    }

    /**
     * outputs a singular and final flag field value
     * based on a given starting point - which may also be 0 (no flag)
     * as a combination of several flags given (with states)
     * this DOES NOT change existing flags, unless they're explicitly specified in another state
     *
     * @param int $flag
     * @param array $flagSettings
     * @return int
     * @throws exception
     */
    public function flagfieldValue(int $flag, array $flagSettings): int
    {
        if (!$this->config->exists('flag')) {
            throw new exception(self::EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL, exception::$ERRORLEVEL_FATAL, null);
        }
        $flags = $this->config->get('flag');
        $validFlagValues = array_values($flags);
        foreach ($flagSettings as $flagval => $state) {
            if (in_array($flagval, $validFlagValues)) {
                if ($state === true) {
                    $flag |= $flagval;
                } elseif ($state === false) {
                    $flag &= ~$flagval;
                } else {
                    // do nothing!
                }
            }
        }
        return $flag;
    }

    /**
     *
     * {@inheritDoc}
     * @param mixed $primaryKey
     * @return array
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::load
     */
    public function load(mixed $primaryKey): array
    {
        return (is_null($primaryKey) ? [] : $this->loadByUnique($this->getPrimaryKey(), $primaryKey));
    }

    /**
     * Loads the given entry as well as the depending on objects
     * @param string $primaryKey
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function loadAll(string $primaryKey): array
    {
        if ($this->config->exists("foreign")) {
            foreach ($this->config->get("foreign") as $reference) {
                $model = app::getModel($reference['model']);
                if (get_class($model) !== get_class($this)) {
                    $this->addModel(app::getModel($reference['model']));
                }
            }
        }
        return $this->addFilter($this->getPrimaryKey(), $primaryKey)->search()->getResult()[0];
    }

    /**
     * [addModel description]
     * @param model $model [description]
     * @param string $type [description]
     * @param string|null $modelField [description]
     * @param string|null $referenceField [description]
     * @return model                 [description]
     * @throws exception
     */
    public function addModel(model $model, string $type = join::TYPE_LEFT, string $modelField = null, string $referenceField = null): model
    {
        $thisKey = null;
        $joinKey = null;

        $conditions = [];

        //
        // model field provided
        //
        if ($modelField != null) {
            // modelField is already provided
            $thisKey = $modelField;

            // look for reference field in foreign key config
            $fkeyConfig = $this->config->get('foreign>' . $modelField);
            if ($fkeyConfig != null) {
                if ($referenceField == null || $referenceField == $fkeyConfig['key']) {
                    $joinKey = $fkeyConfig['key'];
                    $conditions = $fkeyConfig['condition'] ?? [];
                } else {
                    // reference field is not equal
                    // e.g. you're trying to join on unjoinable fields
                    // throw new exception('EXCEPTION_MODEL_SQL_ADDMODEL_INVALID_REFERENCEFIELD', exception::$ERRORLEVEL_ERROR, array($this->getIdentifier(), $referenceField));
                }
            } else {
                // we're missing the foreignkey config for the field provided
                // throw new exception('EXCEPTION_MODEL_SQL_ADDMODEL_UNKNOWN_FOREIGNKEY_CONFIG', exception::$ERRORLEVEL_ERROR, array($this->getIdentifier(), $modelField));
            }
        } elseif ($this->config->exists('foreign')) {
            foreach ($this->config->get('foreign') as $fkeyName => $fkeyConfig) {
                // if we found compatible models
                if ($fkeyConfig['model'] == $model->getIdentifier()) {
                    if (is_array($fkeyConfig['key'])) {
                        $thisKey = array_keys($fkeyConfig['key']);    // current keys
                        $joinKey = array_values($fkeyConfig['key']);  // keys of foreign model
                    } else {
                        $thisKey = $fkeyName;
                        if ($referenceField == null || $referenceField == $fkeyConfig['key']) {
                            $joinKey = $fkeyConfig['key'];
                        }
                    }
                    $conditions = $fkeyConfig['condition'] ?? [];
                    break;
                }
            }
        }

        // Try Reverse Join
        if (($thisKey == null) || ($joinKey == null)) {
            if ($model->config->exists('foreign')) {
                foreach ($model->config->get('foreign') as $fkeyName => $fkeyConfig) {
                    if ($fkeyConfig['model'] == $this->getIdentifier()) {
                        if ($referenceField == null || $referenceField == $fkeyName) {
                            if ($thisKey == null || $thisKey == $fkeyConfig['key']) {
                                $joinKey = $fkeyName;
                            }
                            if ($joinKey == null || $joinKey == $fkeyName) {
                                $thisKey = $fkeyConfig['key'];
                            }
                            $conditions = $fkeyConfig['condition'] ?? [];
                            break;
                        }
                    }
                }
            }
        }

        if (($thisKey == null) || ($joinKey == null)) {
            throw new exception('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION', exception::$ERRORLEVEL_ERROR, [$this->getIdentifier(), $model->getIdentifier(), $modelField, $referenceField]);
        }

        // fallback to bare model joining
        if ($model instanceof dynamic || $this instanceof dynamic) {
            $pluginDriver = 'dynamic';
        } else {
            $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
        }

        //
        // FEATURE/CHANGED 2020-07-21:
        // Added feature 'force_virtual_join' get/setForceVirtualJoin
        // to overcome join limits by some RDBMS like MySQL.
        //
        if ($model->getForceVirtualJoin()) {
            if ($this->getType() == $model->getType()) {
                $pluginDriver = 'dynamic';
            } else {
                $pluginDriver = 'bare';
            }
        }

        //
        // Detect (possible) virtual field configuration right here
        //
        $virtualField = null;
        if (($children = $this->config->get('children')) != null) {
            foreach ($children as $field => $config) {
                if ($config['type'] === 'foreign') {
                    if ($this->config->get('datatype>' . $field) == 'virtual') {
                        if ($thisKey === $config['field']) {
                            $virtualField = $field;
                            break;
                        }
                    }
                }
            }
        }

        $class = '\\codename\\core\\model\\plugin\\join\\' . $pluginDriver;
        $this->nestedModels[] = new $class($model, $type, $thisKey, $joinKey, $conditions, $virtualField);
        // check for already-added ?

        return $this;
    }

    /**
     * [addUseindex description]
     * @param array $fields [description]
     * @return model         [description]
     */
    public function addUseIndex(array $fields): model
    {
        throw new LogicException('Not implemented for this kind of model');
    }

    /**
     * add a custom filter plugin
     * @param filter $filterPlugin [description]
     * @return model                                            [description]
     */
    public function addFilterPlugin(filter $filterPlugin): model
    {
        $this->filter[] = $filterPlugin;
        return $this;
    }

    /**
     * [addFilterPluginCollection description]
     * @param array $filterPlugins [array of filter plugin instances]
     * @param string $groupOperator [operator to be used between all collection items]
     * @param string $groupName [filter group name]
     * @param string|null $conjunction [conjunction to be used inside a filter group]
     * @return model
     * @throws exception
     */
    public function addFilterPluginCollection(array $filterPlugins, string $groupOperator = 'AND', string $groupName = 'default', string $conjunction = null): model
    {
        $filterCollection = [];
        foreach ($filterPlugins as $filter) {
            if ($filter instanceof filterInterface || $filter instanceof managedFilterInterface) {
                $filterCollection[] = $filter;
            } else {
                throw new exception('MODEL_INVALID_FILTER_PLUGIN', exception::$ERRORLEVEL_ERROR);
            }
        }
        if (count($filterCollection) > 0) {
            $this->filterCollections[$groupName][] = [
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction,
            ];
        }
        return $this;
    }

    /**
     * @param string $field
     * @param null $value
     * @param string $operator
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addFilterList, $value, $operator)
     */
    public function addFilterList(string $field, $value = null, string $operator = '=', string $conjunction = null): model
    {
        $class = '\\codename\\core\\model\\plugin\\filterlist\\' . $this->getType();
        // NOTE: the value becomes into model\schematic\sql checked
        $this->filter[] = new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
        return $this;
    }

    /**
     * [addAggregateFilter description]
     * @param string $field [description]
     * @param mixed $value [description]
     * @param string $operator [description]
     * @param string|null $conjunction [description]
     * @return model               [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addAggregateFilter(string $field, mixed $value = null, string $operator = '=', string $conjunction = null): model
    {
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if (is_array($value)) {
            if (count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset');
                return $this;
            }
            $this->aggregateFilter[] = new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            $this->aggregateFilter[] = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
        }
        return $this;
    }

    /**
     * [addDefaultAggregateFilter description]
     * @param string $field [description]
     * @param mixed $value [description]
     * @param string $operator [description]
     * @param string|null $conjunction [description]
     * @return model               [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addDefaultAggregateFilter(string $field, mixed $value = null, string $operator = '=', string $conjunction = null): model
    {
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if (is_array($value)) {
            if (count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset');
                return $this;
            }
            $instance = new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            $instance = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
        }
        $this->aggregateFilter[] = $instance;
        $this->defaultAggregateFilter[] = $instance;
        return $this;
    }

    /**
     * [addAggregateFilterPlugin description]
     * @param aggregatefilter $filterPlugin [description]
     * @return model                                                [description]
     */
    public function addAggregateFilterPlugin(aggregatefilter $filterPlugin): model
    {
        $this->aggregateFilter[] = $filterPlugin;
        return $this;
    }

    /**
     * @param string $field
     * @param string $otherField
     * @param string $operator
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addFilter, $value, $operator)
     */
    public function addFieldFilter(string $field, string $otherField, string $operator = '=', string $conjunction = null): model
    {
        $class = '\\codename\\core\\model\\plugin\\fieldfilter\\' . $this->getType();
        $this->filter[] = new $class($this->getModelfieldInstance($field), $this->getModelfieldInstance($otherField), $operator, $conjunction);
        return $this;
    }

    /**
     * Adds a grouped collection of filters to the underlying filter collection
     * this is used for changing operators (AND/OR/...) and grouping several filters (where statements)
     * @TODO: make this better, could also use value-objects?
     * @param array $filters [array of array( 'field' => ..., 'value' => ... )-elements]
     * @param string $groupOperator [e.g. 'AND' or 'OR']
     * @param string $groupName
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function addFilterCollection(array $filters, string $groupOperator = 'AND', string $groupName = 'default', string $conjunction = null): model
    {
        $filterCollection = [];
        foreach ($filters as $filter) {
            $field = $filter['field'];
            $value = $filter['value'];
            $operator = $filter['operator'];
            $filter_conjunction = $filter['conjunction'] ?? null;
            $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
            if (is_array($value)) {
                if (count($value) == 0) {
                    trigger_error('Empty array filter values have no effect on resultset');
                    continue;
                }
                $filterCollection[] = new $class($this->getModelfieldInstance($field), $value, $operator, $filter_conjunction);
            } else {
                $modelfieldInstance = $this->getModelfieldInstance($field);
                $filterCollection[] = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $filter_conjunction);
            }
        }
        if (count($filterCollection) > 0) {
            $this->filterCollections[$groupName][] = [
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction,
            ];
        }
        return $this;
    }

    /**
     * [addDefaultFilterCollection description]
     * @param array $filters [array of filters]
     * @param string $groupOperator [operator inside the group items]
     * @param string $groupName [name of group to usage across models]
     * @param string|null $conjunction [conjunction of this group, inside the group of same-name filtercollections]
     * @return model                 [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addDefaultFilterCollection(array $filters, string $groupOperator = 'AND', string $groupName = 'default', string $conjunction = null): model
    {
        $filterCollection = [];
        foreach ($filters as $filter) {
            $field = $filter['field'];
            $value = $filter['value'];
            $operator = $filter['operator'];
            $filter_conjunction = $filter['conjunction'] ?? null;
            $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
            if (is_array($value)) {
                if (count($value) == 0) {
                    trigger_error('Empty array filter values have no effect on resultset');
                    continue;
                }
                $filterCollection[] = new $class($this->getModelfieldInstance($field), $value, $operator, $filter_conjunction);
            } else {
                $modelfieldInstance = $this->getModelfieldInstance($field);
                $filterCollection[] = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $filter_conjunction);
            }
        }
        if (count($filterCollection) > 0) {
            $this->defaultfilterCollections[$groupName][] = [
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction,
            ];
            $this->filterCollections[$groupName][] = [
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction,
            ];
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $field
     * @param mixed|null $value
     * @param string $operator
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addDefaultFilter, $value, $operator)
     */
    public function addDefaultFilter(string $field, mixed $value = null, string $operator = '=', string $conjunction = null): model
    {
        $field = $this->getModelfieldInstance($field);
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();

        if (is_array($value)) {
            if (count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset');
                return $this;
            }
            $instance = new $class($field, $value, $operator, $conjunction);
        } else {
            $instance = new $class($field, $this->delimit($field, $value), $operator, $conjunction);
        }
        $this->defaultfilter[] = $instance;
        $this->filter[] = $instance;
        return $this;
    }

    /**
     * Returns the field's value as a string.
     * It delimits the field using a colon if it is required by the field's datatype
     * @param modelfield $field
     * @param mixed|null $value
     * @return mixed
     */
    protected function delimit(modelfield $field, mixed $value = null): mixed
    {
        $fieldtype = $this->getFieldtype($field);

        // CHANGED 2020-12-30 removed \is_string($value) && \strlen($value) == 0
        // Which converted '' to NULL - which is simply wrong.
        if ($value === null) {
            return null;
        }

        if ($fieldtype == 'number') {
            if (is_numeric($value)) {
                return $value;
            }
            if (strlen($value) == 0) {
                return null;
            }
            return $value;
        }
        if ($fieldtype == 'number_natural') {
            if (is_int($value)) {
                return $value;
            }
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
            return (int)$value;
        }
        if ($fieldtype == 'boolean') {
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
            if ($value) {
                return true;
            }
            return false;
        }
        if (str_starts_with($fieldtype, 'text')) {
            if (is_string($value) && strlen($value) == 0) {
                return null;
            }
        }
        return $value;
    }

    /**
     * @param string $field
     * @param null $value
     * @param string $operator
     * @param string|null $conjunction
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addDefaultFilter, $value, $operator)
     */
    public function addDefaultFilterList(string $field, $value = null, string $operator = '=', string $conjunction = null): model
    {
        $field = $this->getModelfieldInstance($field);
        $class = '\\codename\\core\\model\\plugin\\filterlist\\' . $this->getType();

        if (is_array($value)) {
            if (count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset');
                return $this;
            }
            $instance = new $class($field, $value, $operator, $conjunction);
        } else {
            $instance = new $class($field, $this->delimit($field, $value), $operator, $conjunction);
        }
        $this->defaultfilter[] = $instance;
        $this->filter[] = $instance;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $field
     * @param string $order
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addOrder, $order)
     */
    public function addOrder(string $field, string $order = 'ASC'): model
    {
        $field = $this->getModelfieldInstanceRecursive($field) ?? $this->getModelfieldInstance($field);
        if (!$this->fieldExists($field)) {
            // check for existence of a calculated field!
            $found = false;
            foreach ($this->fieldlist as $f) {
                if ($f->field == $field) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new exception(self::EXCEPTION_ADDORDER_FIELDNOTFOUND, exception::$ERRORLEVEL_FATAL, $field);
            }
        }
        $class = '\\codename\\core\\model\\plugin\\order\\' . $this->getType();
        $this->order[] = new $class($field, $order);
        return $this;
    }

    /**
     * Returns a modelfield instance or null
     * by traversing the current nested join tree
     * and identifying the correct schema and table
     *
     * @param string $field [description]
     * @return modelfield|null
     * @throws ReflectionException
     * @throws exception
     */
    protected function getModelfieldInstanceRecursive(string $field): ?modelfield
    {
        $initialInstance = $this->getModelfieldInstance($field);

        // Already defined (schema+table+field)
        if ($initialInstance->getSchema()) {
            return $initialInstance;
        }

        if ($initialInstance->getTable()) {
            // table is already defined, compare to current model and perform checks
            if ($initialInstance->getTable() == $this->table) {
                if (in_array($initialInstance->get(), $this->getFields())) {
                    return $this->getModelfieldInstance($this->schema . '.' . $this->table . '.' . $initialInstance->get());
                }
            }
        } elseif (in_array($initialInstance->get(), $this->getFields())) {
            return $this->getModelfieldInstance($this->schema . '.' . $this->table . '.' . $initialInstance->get());
        }

        // Traverse tree
        foreach ($this->getNestedJoins() as $join) {
            if ($instance = $join->model->getModelfieldInstanceRecursive($field)) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * [addOrderPlugin description]
     * @param order $orderPlugin [description]
     * @return model                                          [description]
     */
    public function addOrderPlugin(order $orderPlugin): model
    {
        $this->order[] = $orderPlugin;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $field
     * @param string|null $alias
     * @return model
     * @throws ReflectionException
     * @throws exception
     * @see model_interface::addField
     */
    public function addField(string $field, string $alias = null): model
    {
        if (str_contains($field, ',')) {
            if ($alias) {
                // This is impossible, multiple fields and a singular alias.
                // We won't support (field1, field2), (alias1, alias2) in this method
                throw new exception('EXCEPTION_ADDFIELD_ALIAS_ON_MULTIPLE_FIELDS', exception::$ERRORLEVEL_ERROR, ['field' => $field, 'alias' => $alias]);
            }
            foreach (explode(',', $field) as $myField) {
                $this->addField(trim($myField));
            }
            return $this;
        }

        $field = $this->getModelfieldInstance($field);
        if (!$this->fieldExists($field)) {
            throw new exception(self::EXCEPTION_ADDFIELD_FIELDNOTFOUND, exception::$ERRORLEVEL_FATAL, $field);
        }

        $class = '\\codename\\core\\model\\plugin\\field\\' . $this->getType();
        $alias = $alias ? $this->getModelfieldInstance($alias) : null;
        $this->fieldlist[] = new $class($field, $alias);
        if (!$alias && in_array($field->getValue(), $this->hiddenFields)) {
            $fieldKey = array_search($field->getValue(), $this->hiddenFields);
            unset($this->hiddenFields[$fieldKey]);
        }
        return $this;
    }

    /**
     * [addVirtualField description]
     * @param string $field [description]
     * @param callable $fieldFunction [description]
     * @return model [this instance]
     */
    public function addVirtualField(string $field, callable $fieldFunction): model
    {
        $this->virtualFields[$field] = $fieldFunction;
        return $this;
    }

    /**
     * [handleVirtualFields description]
     * @param array $dataset [description]
     * @return array          [description]
     */
    public function handleVirtualFields(array $dataset): array
    {
        foreach ($this->virtualFields as $field => $function) {
            $dataset[$field] = $function($dataset);
        }
        return $dataset;
    }

    /**
     * [hideAllFields description]
     * @return model [description]
     */
    public function hideAllFields(): model
    {
        foreach ($this->getFields() as $field) {
            $this->hideField($field);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see model_interface::hideField
     */
    public function hideField(string $field): model
    {
        if (str_contains($field, ',')) {
            foreach (explode(',', $field) as $myField) {
                $this->hideField(trim($myField));
            }
            return $this;
        }
        $this->hiddenFields[] = $field;
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     * @throws ReflectionException
     * @throws exception
     */
    public function addGroup(string $field): model
    {
        $field = $this->getModelfieldInstance($field);
        $aliased = false;
        if (!$this->fieldExists($field)) {
            $foundInFieldlist = false;
            foreach ($this->fieldlist as $checkField) {
                if ($checkField->field->get() == $field->get()) {
                    $foundInFieldlist = true;

                    // At this point, check for 'virtuality' of a field
                    // e.g. aliased, calculated and aggregates
                    // (the latter ones are usually calculated fields)
                    //
                    if ($checkField instanceof calculatedfield || $checkField instanceof aggregate) {
                        $aliased = true;
                    }
                    break;
                }
            }
            if ($foundInFieldlist === false) {
                throw new exception(self::EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST, exception::$ERRORLEVEL_FATAL, [$field, $this->fieldlist]);
            }
        }
        $class = '\\codename\\core\\model\\plugin\\group\\' . $this->getType();
        $groupInstance = new $class($field);
        $groupInstance->aliased = $aliased;
        $this->group[] = $groupInstance;
        return $this;
    }

    /**
     * @param string $field
     * @param string $calculation
     * @return $this
     * @throws ReflectionException
     * @throws exception
     */
    public function addCalculatedField(string $field, string $calculation): model
    {
        $field = $this->getModelfieldInstance($field);
        // only check for EXISTENCE of the fieldname, cancel if so - we don't want duplicates!
        if ($this->fieldExists($field)) {
            throw new exception(self::EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS, exception::$ERRORLEVEL_FATAL, $field);
        }
        $class = '\\codename\\core\\model\\plugin\\calculatedfield\\' . $this->getType();
        $this->fieldlist[] = new $class($field, $calculation);
        return $this;
    }

    /**
     * [removeCalculatedField description]
     * @param string $field [description]
     * @return model         [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function removeCalculatedField(string $field): model
    {
        $field = $this->getModelfieldInstance($field);
        $this->fieldlist = array_filter($this->fieldlist, function ($item) use ($field) {
            if ($item instanceof calculatedfield) {
                if ($item->field->get() == $field->get()) {
                    return false;
                }
            }
            return true;
        });
        return $this;
    }

    /**
     * adds a field that uses aggregate functions to be calculated
     *
     * @param string $field [description]
     * @param string $calculationType [description]
     * @param string $fieldBase [description]
     * @return model                   [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addAggregateField(string $field, string $calculationType, string $fieldBase): model
    {
        $field = $this->getModelfieldInstance($field);
        $fieldBase = $this->getModelfieldInstance($fieldBase);
        // only check for EXISTENCE of the fieldname, cancel if so - we don't want duplicates!
        if ($this->fieldExists($field)) {
            throw new exception(self::EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS, exception::$ERRORLEVEL_FATAL, $field);
        }
        $class = '\\codename\\core\\model\\plugin\\aggregate\\' . $this->getType();
        $this->fieldlist[] = new $class($field, $calculationType, $fieldBase);
        return $this;
    }

    /**
     * [addFulltextField description]
     * @param string $field [description]
     * @param string $value [description]
     * @param mixed $fields [description]
     * @return model          [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addFulltextField(string $field, string $value, mixed $fields): model
    {
        $field = $this->getModelfieldInstance($field);
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        if (count($fields) === 0) {
            throw new exception(self::EXCEPTION_ADDFULLTEXTFIELD_NO_FIELDS_FOUND, exception::$ERRORLEVEL_FATAL, $fields);
        }
        $thisFields = [];
        foreach ($fields as $resultField) {
            $thisFields[] = $this->getModelfieldInstance(trim($resultField));
        }
        $class = '\\codename\\core\\model\\plugin\\fulltext\\' . $this->getType();
        $this->fieldlist[] = new $class($field, $value, $thisFields);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see model_interface::setOffset
     */
    public function setOffset(int $offset): model
    {
        $class = '\\codename\\core\\model\\plugin\\offset\\' . $this->getType();
        $this->offset = new $class($offset);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilterDuplicates(bool $state): model
    {
        $this->filterDuplicates = $state;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see model_interface::setCache
     */
    public function useCache(): model
    {
        $this->cache = true;
        return $this;
    }

    /**
     * Returns a list of fields (as strings)
     * that are part of the current model
     * or were added dynamically (aliased, function-based or implicit)
     * and handles hidden fields, too.
     *
     * By the way, virtual fields are *not* returned by this function
     * As the framework defines virtual fields as only to be existent
     * if the corresponding join is also used.
     *
     * @return string[]
     */
    public function getCurrentAliasedFieldlist(): array
    {
        $result = [];
        if (count($this->fieldlist) == 0 && count($this->hiddenFields) > 0) {
            //
            // Include all fields but specific ones
            //
            foreach ($this->getFields() as $fieldName) {
                if ($this->config->get('datatype>' . $fieldName) !== 'virtual') {
                    if (!in_array($fieldName, $this->hiddenFields)) {
                        $result[] = $fieldName;
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
                    // Get the field's alias
                    //
                    $result[] = $field->field->get();
                } elseif ($field instanceof aggregateInterface) {
                    //
                    // aggregate field's alias
                    //
                    $result[] = $field->field->get();
                } elseif ($field instanceof fulltextInterface) {
                    //
                    // fulltext field's alias
                    //
                    $result[] = $field->field->get();
                } elseif ($this->config->get('datatype>' . $field->field->get()) !== 'virtual' && (!in_array($field->field->get(), $this->hiddenFields) || $field->alias)) {
                    //
                    // omit virtual fields
                    // they're not part of the DB.
                    //
                    $fieldAlias = $field->alias?->get();
                    if ($fieldAlias) {
                        $result[] = $fieldAlias;
                    } else {
                        $result[] = $field->field->get();
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
                        $result[] = $fieldName;
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
            // The rest of the fields. Skip virtual fields
            //
            foreach ($this->getFields() as $fieldName) {
                if ($this->config->get('datatype>' . $fieldName) !== 'virtual') {
                    $result[] = $fieldName;
                }
            }
        }

        return array_values($result);
    }

    /**
     * Enables virtual field result functionality on this model instance
     * @param bool $state [description]
     * @return model        [description]
     */
    public function setVirtualFieldResult(bool $state): model
    {
        return $this;
    }

    /**
     * returns an array of virtual fields (names) currently configured
     * @return array [description]
     */
    public function getVirtualFields(): array
    {
        return array_keys($this->virtualFields);
    }

    /**
     * Validates the data array and returns true if no errors occurred
     * @param array $data
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function isValid(array $data): bool
    {
        return (count($this->validate($data)->getErrors()) == 0);
    }

    /**
     * normalizes data in the given array.
     * Tries to identify complex datastructures by the Hidden $FIELDNAME."_" fields and makes objects of them
     * @param array $data
     * @return array
     * @throws exception
     */
    public function normalizeData(array $data): array
    {
        $myData = [];

        $flagFieldName = $this->table . '_flag';

        if (!$this->normalizeDataFieldCache) {
            $this->normalizeDataFieldCache = $this->getFields();
        }

        foreach ($this->normalizeDataFieldCache as $field) {
            if ($field == $flagFieldName) {
                if (array_key_exists($flagFieldName, $data)) {
                    if (!is_array($data[$flagFieldName])) {
                        //
                        // CHANGED 2021-09-21: flag field values may be passed-through
                        // if not in array-format
                        // TODO: validate flags?
                        //
                        $myData[$field] = $data[$flagFieldName];
                        continue;
                    }

                    $flagval = 0;
                    foreach ($data[$flagFieldName] as $flagname => $status) {
                        $currflag = $this->config->get("flag>$flagname");
                        if (is_null($currflag) || !$status) {
                            continue;
                        }
                        $flagval |= $currflag;
                    }
                    $myData[$field] = $flagval;
                } else {
                    unset($data[$field]);
                }
                continue;
            }

            // Otherwise the field exists in the data object
            if (array_key_exists($field, $data)) {
                $myData[$field] = $this->importFieldImproved($field, $data[$field]);
            }
        }
        return $myData;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    protected function importFieldImproved(string $field, mixed $value = null): mixed
    {
        $fieldType = $this->fieldTypeCache[$field] ?? $this->fieldTypeCache[$field] = $this->getFieldtypeImproved($field);
        switch ($fieldType) {
            case 'number_natural':
                if (is_string($value) && strlen($value) === 0) {
                    return null;
                }
                break;
            case 'boolean':
                // allow null booleans
                // may be needed for conditional unique keys
                if (is_null($value)) {
                    return $value;
                }
                // pure boolean
                if (is_bool($value)) {
                    return $value;
                }
                // int: 0 or 1
                if (is_int($value)) {
                    if ($value !== 1 && $value !== 0) {
                        throw new exception('EXCEPTION_MODEL_IMPORTFIELD_BOOLEAN_INVALID', exception::$ERRORLEVEL_ERROR, [
                          'field' => $field,
                          'value' => $value,
                        ]);
                    }
                    return $value === 1;
                }
                // string boolean
                if (is_string($value)) {
                    // fallback, empty string
                    if (strlen($value) === 0) {
                        return null;
                    }
                    if ($value === '1') {
                        return true;
                    } elseif ($value === '0') {
                        return false;
                    } elseif ($value === 'true') {
                        return true;
                    } elseif ($value === 'false') {
                        return false;
                    }
                }
                // fallback
                return false;
            case 'text_date':
                if (is_null($value)) {
                    return $value;
                }
                // automatically convert input value
                return (new DateTime($value))->format('Y-m-d');
        }
        return $value;
    }

    /**
     * Returns the given $flagname's flag integer value
     * @param string $flagname
     * @return mixed
     * @throws exception
     */
    public function getFlag(string $flagname): mixed
    {
        if (!$this->config->exists("flag>$flagname")) {
            throw new exception(self::EXCEPTION_GETFLAG_FLAGNOTFOUND, exception::$ERRORLEVEL_FATAL, $flagname);
        }
        return $this->config->get("flag>$flagname");
    }

    /**
     * Returns true if the given flag name is set to true in the data array. Returns false otherwise
     * @param int $flagvalue
     * @param array $data
     * @return bool
     * @throws exception
     * @todo Validate the flag in the model constructor (model configurator)
     * @todo add \codename\core\exceptions
     */
    public function isFlag(int $flagvalue, array $data): bool
    {
        $flagField = $this->getIdentifier() . '_flag';
        if (!array_key_exists($flagField, $data)) {
            throw new exception(self::EXCEPTION_ISFLAG_NOFLAGFIELD, exception::$ERRORLEVEL_FATAL, ['field' => $flagField, 'data' => $data]);
        }
        return (($data[$flagField] & $flagvalue) == $flagvalue);
    }

    /**
     * Converts the storage format into a human-readable format
     * @param modelfield $field
     * @param mixed|null $value
     * @return mixed
     */
    public function exportField(modelfield $field, mixed $value = null): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        return match ($this->getFieldtype($field)) {
            'boolean' => (bool)$value,
            'text_date' => date('Y-m-d', strtotime($value)),
            default => $value,
        };
    }

    /**
     * returns the last query performed and stored.
     * @return string
     */
    public function getLastQuery(): string
    {
        return $this->lastQuery;
    }

    /**
     * Returns the lastInsertId returned from db driver
     * May contain foreign ids.
     * @return int|string|bool|null
     */
    public function lastInsertId(): int|string|bool|null
    {
        return $this->db->lastInsertId();
    }

    /**
     * function is required to remove the default filter from the number generator
     * @return model [type] [description]
     */
    public function removeDefaultFilter(): static
    {
        $this->defaultfilter = [];
        $this->defaultAggregateFilter = [];
        $this->defaultflagfilter = [];
        $this->defaultfilterCollections = [];
        return $this;
    }

    /**
     * loads a new config file (uncached)
     * implement me!
     * @return config
     */
    abstract protected function loadConfig(): config;

    /**
     * Whether model is discrete/self-contained
     * and/or performs its work as a subquery
     * @return bool [description]
     */
    protected function isDiscreteModel(): bool
    {
        return false;
    }

    /**
     * Initiates a servicing instance for this model
     * @return void
     */
    protected function initServicingInstance(): void
    {
        // no implementation for base model
    }

    /**
     * [getFieldlistArray description]
     * @param field[] $fields [description]
     * @return array         [description]
     */
    protected function getFieldlistArray(array $fields): array
    {
        $returnFields = [];
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                if ($field->alias ?? false) {
                    $returnFields[] = $field->alias->get();
                } else {
                    $returnFields[] = $field->field->get();
                }
            }
        }

        // filter out hidden fields by difference calculation
        return array_diff($returnFields, $this->hiddenFields);
    }

    /**
     * Perform the given query and save the result in the instance
     * @param string $query
     * @param array $params
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function doQuery(string $query, array $params = []): void
    {
        $cacheObj = null;
        $cacheGroup = null;
        $cacheKey = null;
        // if cached, load it
        if ($this->cache) {
            $cacheObj = app::getCache();
            $cacheGroup = $this->getCacheGroup();
            $cacheKey = "manualcache" . md5(
                serialize(
                    [
                      get_class($this),
                      $this->getIdentifier(),
                      $query,
                      $this->getCurrentCacheIdentifierParameters(),
                      $params,
                    ]
                )
            );

            $this->result = $cacheObj->get($cacheGroup, $cacheKey);

            if (is_array($this->result)) {
                $this->reset();
                return;
            }
        }

        $this->result = $this->internalQuery($query, $params);

        // save last query
        if ($this->saveLastQuery) {
            $this->lastQuery = $query;
        }

        // if cached, save it
        if ($this->cache) {
            $result = $this->getResult();
            if (count($result) > 0) {
                $cacheObj->set($cacheGroup, $cacheKey, $result);
            }
        }
        $this->reset();
    }

    /**
     * Returns the default cache client
     * @return cache
     * @throws ReflectionException
     * @throws exception
     */
    protected function getCache(): cache
    {
        return app::getCache();
    }

    /**
     * Returns the cachegroup identifier for this model
     * @return string
     * @todo prevent collision by using the PSR-4 namespace from ReflectionClass::
     */
    protected function getCacheGroup(): string
    {
        return get_class($this);
    }

    /**
     * [getCurrentCacheIdentifierParameters description]
     * @return array [description]
     */
    protected function getCurrentCacheIdentifierParameters(): array
    {
        $params = [];
        $params['filter'] = $this->filter;
        $params['filtercollections'] = $this->filterCollections;
        foreach ($this->getNestedJoins() as $join) {
            //
            // CHANGED 2021-09-24: nested model's join plugin parameters were not correctly incorporated into cache key
            //
            $params['nest'][] = [
              'cacheIdentifier' => $join->model->getCurrentCacheIdentifierParameters(),
              'model' => $join->model->getIdentifier(),
              'cacheParameters' => $join->getCurrentCacheIdentifierParameters(),
            ];
        }
        return $params;
    }

    /**
     * internal query
     */
    abstract protected function internalQuery(string $query, array $params = []);

    /**
     * Deletes dependencies of elements in this model
     * @param string $primaryKey
     * @return void
     */
    protected function deleteChildren(string $primaryKey): void
    {
    }
}
