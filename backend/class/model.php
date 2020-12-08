<?php
namespace codename\core;
use codename\core\exception;
use codename\core\model\plugin;

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
abstract class model implements \codename\core\model\modelInterface {

    /**
     * You want to set a field that is not present in the model
     * @var string
     */
    CONST EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL';

    /**
     * You want to set a field of an entry, but there is no entry loaded, yet
     * @var string
     */
    CONST EXCEPTION_FIELDSET_NOOBJECTLOADED = 'EXCEPTION_FIELDSET_NOOBJECTLOADED';

    /**
     * You want to get the content of a field but the desired field is not available
     * @var string
     */
    CONST EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL';

    /**
     * You want to get the value of a field but there is no object loaded currently
     * @var string
     */
    CONST EXCEPTION_FIELDGET_NOOBJECTLOADED = 'EXCEPTION_FIELDGET_NOOBJECTLOADED';

    /**
     * You want to delete an entry but you did not load the entry, yet
     * @var string
     */
    CONST EXCEPTION_ENTRYDELETE_NOOBJECTLOADED = 'EXCEPTION_ENTRYDELETE_NOOBJECTLOADED';

    /**
     * You want to save an entry but you did not load it, yet.
     * @var string
     */
    CONST EXCEPTION_ENTRYSAVE_NOOBJECTLOADED = 'EXCEPTION_ENTRYSAVE_NOOBJECTLOADED';

    /**
     * Entry load failed (wrong ID or inaccessible)
     * @var string
     */
    CONST EXCEPTION_ENTRYLOAD_FAILED = 'EXCEPTION_ENTRYLOAD_FAILED';

    /**
     * You want to update an element, but it seems that the current element is empty
     * @var string
     */
    CONST EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY = 'EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY';

    /**
     * You want to update an element there is no object availabe in the current resource
     * @var string
     */
    CONST EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED = 'EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED';

    /**
     * You want to set the flag of an entry, but you did not load an entry
     * @var string
     */
    CONST EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED = 'EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED';

    /**
     * The loaded entry does not contain flags
     * @var string
     */
    CONST EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL';

    /**
     * You want to unset a flag but the element is empty
     * @var string
     */
    CONST EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED = 'EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED';

    /**
     * You want to unset a flag but there are no flags in this model
     * @var string
     */
    CONST EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL';

    /**
     * Exception thrown if an invalid flag value is provided
     * @var string
     */
    CONST EXCEPTION_INVALID_FLAG_VALUE = 'EXCEPTION_INVALID_FLAG_VALUE';

    /**
     * You want to get a flag field value, but there are no flags in this model
     * @var string
     */
    CONST EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL = 'EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL';



    /**
     * You want to add a default filter but the field was not found
     * @var string
     */
    CONST EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND = 'EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND';

    /**
     * You want to add an order object but the field does not exist in the model
     * @var string
     */
    CONST EXCEPTION_ADDORDER_FIELDNOTFOUND = 'EXCEPTION_ADDORDER_FIELDNOTFOUND';

    /**
     * The field you want to add to the response is not available in the model
     * @var string
     */
    CONST EXCEPTION_ADDFIELD_FIELDNOTFOUND = 'EXCEPTION_ADDFIELD_FIELDNOTFOUND';

    /**
     * The field you want to add to the response is not available in the model
     * @var string
     */
    CONST EXCEPTION_HIDEFIELD_FIELDNOTFOUND = 'EXCEPTION_HIDEFIELD_FIELDNOTFOUND';

    /**
     * You want to know the primary key, but it remains unset from the configuration
     * @var string
     */
    CONST EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG = 'EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG';

    /**
     * You want to get the flag of an entry but the given flag was not found
     * @var string
     */
    CONST EXCEPTION_GETFLAG_FLAGNOTFOUND = 'EXCEPTION_GETFLAG_FLAGNOTFOUND';

    /**
     * The model is missing a flag field.
     * @var string
     */
    CONST EXCEPTION_ISFLAG_NOFLAGFIELD = 'EXCEPTION_ISFLAG_NOFLAGFIELD';

    /**
     * Incompatible models during autocombineModels
     * @var string
     */
    CONST EXCEPTION_AUTOCOMBINEMODELS_UNJOINABLE_MODELS = "EXCEPTION_AUTOCOMBINEMODELS_UNJOINABLE_MODELS";

    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    CONST DB_TYPE = null;

    /**
     * Set to true if the query shall be cached after finishing.
     * @var boolean
     */
    protected $cache = false;

    /**
     * Contains the driver that is used to load the PDO class
     * @var string
     */
    protected $driver = null;

    /**
     * array contains the result of the given query
     * @var array $result
     */
    protected $result = null;

    /**
     * add a index for the function use index
     * @var string[]
     */
    protected $useIndex = [];

    /**
     * Contains instances of the filters for the model request
     * @var \codename\core\model\plugin\filter[] $filter
     */
    protected $filter = array();

    /**
     * Contains instances of the filters that will be used again after resetting the model
     * @var array $filter
     */
    protected $defaultfilter = array();

    /**
     * Contains instances of aggregate filters for the model request
     * @var \codename\core\model\plugin\aggregatefilter[] $aggregateFilter
     */
    protected $aggregateFilter = array();

    /**
     * Contains instances of default (reused) aggregate filters for the model request
     * @var \codename\core\model\plugin\aggregatefilter[] $defaultAggregateFilter
     */
    protected $defaultAggregateFilter = array();

    /**
     * Contains an array of integer values for binary checks against the flag field
     * @var array $flagfilter
    **/
    protected $flagfilter = array();

    /**
     * Like flagfilter, but retains its value through a reset
     * @var array $flagfilter
    **/
    protected $defaultflagfilter = array();

    /**
     * Contains the instances of the order directives for the model request
     * @var array $filter
     */
    protected $order = array();

    /**
     * Contains the list of fields that shall be returned
     * @var array
     */
    protected $fieldlist = array();

    /**
     * Contains the list of fields to be hidden in result
     * @var array
     */
    protected $hiddenFields = array();

    /**
     * Contains the instance of the limitation data for the model request
     * @var model_plugin_limit $limit
     */
    protected $limit = null;

    /**
     * Contains the instance of the offset data for the model request
     * @var model_plugin_offset
     */
    protected $offset = null;

    /**
     * Duplicate filtering state
     * @var bool
     */
    protected $filterDuplicates = false;

    /**
     * Contains the database connection
     * @var \codename\core\database
     */
    protected $db = null;

    /**
     * Contains the application this model is originated in for file system operations
     * @var string
     */
    protected $appname = null;

    /**
     * Contains the delimiter for strings
     * @var string $delimiter
     */
    protected $delimiter = "'";


    /**
     * Contains the errorstack for this instance
     * @var \codename\core\errorstack
     */
    protected $errorstack = null;

    /**
     * Contaions the datacontainer object
     * @var \codename\core\datacontainer
     */
    protected $data = null;

    /**
     * Contains the configuration
     * @var \codename\core\config
     */
    public $config = null;

    /**
     * returns the config object
     * @return \codename\core\config [description]
     */
    public function getConfig() : \codename\core\config {
      return $this->config;
    }

    /**
     * loads a new config file (uncached)
     * implement me!
     * @return \codename\core\config
     */
    protected abstract function loadConfig() : \codename\core\config;

    /**
     * @inheritDoc
     */
    public function getCount(): int
    {
      //
      // NOTE: this has to be implemented per DB technology
      //
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * [getNestedJoins description]
     * @param  string|null  $model                  name of a model to look for
     * @param  string|null  $modelField             name of a field the model is joined upon
     * @return \codename\core\model\plugin\join[]   [array of joins, may be empty]
     */
    public function getNestedJoins(string $model = null, string $modelField = null) : array {
      if($model || $modelField) {
        return array_values(array_filter($this->getNestedJoins(), function(\codename\core\model\plugin\join $join) use ($model, $modelField){
          return ($model === null || $join->model->getIdentifier() === $model) && ($modelField === null || $join->modelField === $modelField);
        }));
      } else {
        return $this->nestedModels;
      }
    }

    /**
     * [getNestedCollections description]
     * @return \codename\core\model\plugin\collection[] [description]
     */
    public function getNestedCollections() : array {
      return $this->collectionPlugins;
    }

    /**
     * determines if the model is joinable
     * in the same run (e.g. DB compatibility and stuff)
     * @param  \codename\core\model $model [the model to check direct join compatibility with]
     * @return bool
     */
    protected function compatibleJoin(\codename\core\model $model) : bool {
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
     * Whether model is discrete/self-contained
     * and/or performs its work as a subquery
     * @return bool [description]
     */
    protected function isDiscreteModel() : bool {
      return false;
    }

    /**
     * I will set the given $field's value to $value of the previously loaded dataset / entry.
     * @param \codename\core\value\text\modelfield $field
     * @param multitype $value
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function fieldSet(\codename\core\value\text\modelfield $field, $value) : \codename\core\model {
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        }
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDSET_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL);
        }
        $this->data->setData($field->get(), $value);
        return $this;
    }

    /**
     * I will return the given $field's value of the previously loaded dataset.
     * @param \codename\core\value\text\modelfield $field
     * @throws \codename\core\exception
     * @return \codename\core\multitype
     */
    public function fieldGet(\codename\core\value\text\modelfield $field) {
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        }
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDGET_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL);
        }
        return $this->data->getData($field->get());
    }

    /**
     * I am capable of creating a new entry for the current model by the given array $data.
     * @param array $data
     * @return \codename\core\model
     */
    public function entryMake(array $data = array()) : \codename\core\model {
        $this->data = new \codename\core\datacontainer($data);
        return $this;
    }

    /**
     * I will validate the currently loaded dataset of the current model and return the array of errors that might have occured
     * @return array
     */
    public function entryValidate() : array {
        return $this->validate($this->data->getData())->getErrors();
    }

    /**
     * I will delete the previously loaded entry.
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entryDelete() : \codename\core\model {
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYDELETE_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL);
        }
        $this->delete($this->data->getData($this->getPrimarykey()));
        return $this;
    }

    /**
     * [getData description]
     * @return array [description]
     */
    public function getData() : array {
      return $this->data->getData();
    }

    /**
     * [protected description]
     * @var \codename\core\model\plugin\collection[]
     */
    protected $collectionPlugins = [];

    /**
     * [addCollectionModel description]
     * @param \codename\core\model $model      [description]
     * @param string|null          $modelField [description]
     * @return \codename\core\model
     */
    public function addCollectionModel(\codename\core\model $model, string $modelField = null) : \codename\core\model {
      if($this->config->exists('collection')) {

        $collectionConfig = null;

        //
        // try to determine modelfield by the best-matching collection
        //
        if(!$modelField) {
          if($this->config->exists('collection')) {
            foreach($this->config->get('collection') as $collectionFieldName => $config) {
              if($config['model'] === $model->getIdentifier()) {
                $modelField = $collectionFieldName;
                $collectionConfig = $config;
              }
            }
          }
        }

        //
        // Still no modelfield
        //
        if(!$modelField) {
          throw new exception('EXCEPTION_UNKNOWN_COLLECTION_MODEL', exception::$ERRORLEVEL_ERROR, [$this->getIdentifier(), $model->getIdentifier()]);
        }

        //
        // Case where we haven't retrieved the collection config yet
        //
        if(!$collectionConfig) {
          $collectionConfig = $this->config->get('collection>'.$modelField);
        }

        //
        // Still no collection config
        //
        if(!$collectionConfig) {
          throw new exception('EXCEPTION_NO_COLLECTION_CONFIG', exception::$ERRORLEVEL_ERROR, $modelField);
        }

        if($collectionConfig['model'] != $model->getIdentifier()) {
          throw new exception('EXCEPTION_MODEL_ADDCOLLECTIONMODEL_INCOMPATIBLE', exception::$ERRORLEVEL_ERROR, [$collectionConfig['model'], $model->getIdentifier()]);
        }

        $modelFieldInstance = $this->getModelfieldInstance($modelField);

        // Finally, add model
        $this->collectionPlugins[$modelFieldInstance->get()] = new \codename\core\model\plugin\collection(
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
     * [addModel description]
     * @param  \codename\core\model   $model          [description]
     * @param  string                 $type           [description]
     * @param  string|null            $modelField     [description]
     * @param  string|null            $referenceField [description]
     * @return \codename\core\model                 [description]
     */
    public function addModel(\codename\core\model $model, string $type = plugin\join::TYPE_LEFT, string $modelField = null, string $referenceField = null) : \codename\core\model {

        $thisKey = null;
        $joinKey = null;

        $conditions = [];

        // model field provided
        //
        //
        if($modelField != null) {
          // modelField is already provided
          $thisKey = $modelField;

          // look for reference field in foreign key config
          $fkeyConfig = $this->config->get('foreign>'.$modelField);
          if($fkeyConfig != null) {
            if($referenceField == null || $referenceField == $fkeyConfig['key']) {
              $joinKey = $fkeyConfig['key'];
              $conditions = $fkeyConfig['condition'] ?? [];
            } else {
              // reference field is not equal
              // e.g. you're trying to join on unjoinable fields
              // throw new exception('EXCEPTION_MODEL_SQL_ADDMODEL_INVALID_REFERENCEFIELD', exception::$ERRORLEVEL_ERROR, array($this->getIdentifer(), $referenceField));
            }
          } else {
            // we're missing the foreignkey config for the field provided
            // throw new exception('EXCEPTION_MODEL_SQL_ADDMODEL_UNKNOWN_FOREIGNKEY_CONFIG', exception::$ERRORLEVEL_ERROR, array($this->getIdentifer(), $modelField));
          }
        } else {
          // search for modelfield, as it is null
          if($this->config->exists('foreign')) {
            foreach($this->config->get('foreign') as $fkeyName => $fkeyConfig) {
              // if we found compatible models
              if($fkeyConfig['model'] == $model->getIdentifier()) {
                if(is_array($fkeyConfig['key'])) {
                  $thisKey = array_keys($fkeyConfig['key']);    // current keys
                  $joinKey = array_values($fkeyConfig['key']);  // keys of foreign model
                } else {
                  $thisKey = $fkeyName;
                  if($referenceField == null || $referenceField == $fkeyConfig['key']) {
                    $joinKey = $fkeyConfig['key'];
                  }
                }
                $conditions = $fkeyConfig['condition'] ?? [];
                break;
              }
            }
          }
        }

        // Try Reverse Join
        if(($thisKey == null) || ($joinKey == null)) {
          if($model->config->exists('foreign')) {
            foreach($model->config->get('foreign') as $fkeyName => $fkeyConfig) {
              if($fkeyConfig['model'] == $this->getIdentifier()) {
                if($referenceField == null || $referenceField == $fkeyName) {
                  if($thisKey == null || $thisKey == $fkeyConfig['key']) {
                    $joinKey = $fkeyName;
                  }
                  if($joinKey == null || $joinKey == $fkeyName) {
                    $thisKey = $fkeyConfig['key'];
                  }
                  $conditions = $fkeyConfig['condition'] ?? [];
                  // $thisKey = $fkeyConfig['key'];
                  // $joinKey = $fkeyName;
                  break;
                }
              }
            }
          }
        }

        if(($thisKey == null) || ($joinKey == null)) {
          throw new exception('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION', exception::$ERRORLEVEL_ERROR, array($this->getIdentifier(), $model->getIdentifier(), $modelField, $referenceField));
        }

        // fallback to bare model joining
        if($model instanceof \codename\core\model\schemeless\dynamic || $this instanceof \codename\core\model\schemeless\dynamic) {
          $pluginDriver = 'dynamic';
        } else {
          $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
        }

        //
        // FEATURE/CHANGED 2020-07-21:
        // Added feature 'force_virtual_join' get/setForceVirtualJoin
        // to overcome join limits by some RDBMS like MySQL.
        //
        if($model->getForceVirtualJoin()) {
          if($this->getType() == $model->getType()) {
            $pluginDriver = 'dynamic';
          } else {
            $pluginDriver = 'bare';
          }
        }

        //
        // Detect (possible) virtual field configuration right here
        //
        $virtualField = null;
        if(($children = $this->config->get('children')) != null) {
          foreach($children as $field => $config) {
            if($config['type'] === 'foreign') {
              $foreign = $this->config->get('foreign>'.$config['field']);
              if($this->config->get('datatype>'.$field) == 'virtual') {
                if($thisKey === $config['field']) {
                  $virtualField = $field;
                  break;
                }
              }
            }
          }
        }

        $class = '\\codename\\core\\model\\plugin\\join\\' . $pluginDriver;
        array_push($this->nestedModels, new $class($model, $type, $thisKey, $joinKey, $conditions, $virtualField));
        // check for already-added ?

        return $this;
    }

    /**
     * state of force_virtual_join feature
     * @var bool
     */
    protected $forceVirtualJoin = false;

    /**
     * Sets the force_virtual_join feature state
     * This enables the model to be joined virtually
     * to avoid join limits of various RDBMS
     * @param bool $state
     */
    public function setForceVirtualJoin(bool $state) {
      $this->forceVirtualJoin = $state;
      return $this;
    }

    /**
     * Gets the current state of the force_virtual_join feature
     * @return bool
     */
    public function getForceVirtualJoin() : bool {
      return $this->forceVirtualJoin;
    }

    /**
     * adds a model using custom parameters
     * and optionally using custom extra conditions
     *
     * this can be used to join models that have no explicit foreign key reference to each other
     *
     * @param  \codename\core\model     $model          [description]
     * @param  string                   $type           [description]
     * @param  string|null              $modelField     [description]
     * @param  string|null              $referenceField [description]
     * @param  array                    $conditions
     * @return \codename\core\model                 [description]
     */
    public function addCustomJoin(\codename\core\model $model, string $type = plugin\join::TYPE_LEFT, ?string $modelField = null, ?string $referenceField = null, array $conditions = []) : \codename\core\model {
      $thisKey = $modelField;
      $joinKey = $referenceField;

      // fallback to bare model joining
      if($model instanceof \codename\core\model\schemeless\dynamic || $this instanceof \codename\core\model\schemeless\dynamic) {
        $pluginDriver = 'dynamic';
      } else {
        $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
      }

      $class = '\\codename\\core\\model\\plugin\\join\\' . $pluginDriver;
      array_push($this->nestedModels, new $class($model, $type, $thisKey, $joinKey, $conditions));
      return $this;
    }

    /**
     * [addRecursiveModel description]
     * @param  \codename\core\model     $model              [model instance to recurse]
     * @param  string                   $selfReferenceField [field used for self-referencing]
     * @param  string                   $anchorField        [field used as anchor point]
     * @param  array                    $anchorConditions   [additional anchor conditions - e.g. the starting point]
     * @param  string                   $type               [type of join]
     * @param  string|null              $modelField         [description]
     * @param  string|null              $referenceField     [description]
     * @param  array                    $conditions         [description]
     * @return \codename\core\model                     [description]
     */
    public function addRecursiveModel(\codename\core\model $model, string $selfReferenceField, string $anchorField, array $anchorConditions, string $type = plugin\join::TYPE_LEFT, ?string $modelField = null, ?string $referenceField = null, array $conditions = []) : \codename\core\model {
      $thisKey = $modelField;
      $joinKey = $referenceField;

      // TODO: auto-determine modelField and referenceField / thisKey and joinKey

      if((!$model->config->get('foreign>'.$selfReferenceField.'>model') == $model->getIdentifier()
        || !$model->config->get('foreign>'.$selfReferenceField.'>key') == $model->getPrimaryKey())
        && (!$model->config->get('foreign>'.$anchorField.'>model') == $model->getIdentifier()
        || !$model->config->get('foreign>'.$anchorField.'>key') == $model->getPrimaryKey())
      ) {
        throw new exception('INVALID_RECURSIVE_MODEL_JOIN', exception::$ERRORLEVEL_ERROR);
      }

      // fallback to bare model joining
      if($model instanceof \codename\core\model\schemeless\dynamic || $this instanceof \codename\core\model\schemeless\dynamic) {
        $pluginDriver = 'dynamic';
      } else {
        $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';
      }

      $class = '\\codename\\core\\model\\plugin\\join\\recursive\\' . $pluginDriver;
      array_push($this->nestedModels, new $class($model, $selfReferenceField, $anchorField, $anchorConditions, $type, $thisKey, $joinKey, $conditions));
      return $this;
    }

    /**
     * [setRecursive description]
     * @param  string               $selfReferenceField [description]
     * @param  string               $anchorField        [description]
     * @param  array                $anchorConditions   [description]
     * @return \codename\core\model                     [description]
     */
    public function setRecursive(string $selfReferenceField, string $anchorField, array $anchorConditions): \codename\core\model {

      if($this->recursive) {
        // kill, already active?
        throw new exception('EXCEPTION_MODEL_SETRECURSIVE_ALREADY_ENABLED', exception::$ERRORLEVEL_ERROR);
      }

      $this->recursive = true;

      if((!$this->config->get('foreign>'.$selfReferenceField.'>model') == $this->getIdentifier()
        || !$this->config->get('foreign>'.$selfReferenceField.'>key') == $this->getPrimaryKey())
        && (!$this->config->get('foreign>'.$anchorField.'>model') == $this->getIdentifier()
        || !$this->config->get('foreign>'.$anchorField.'>key') == $this->getPrimaryKey())
      ) {
        throw new exception('INVALID_RECURSIVE_MODEL_CONFIG', exception::$ERRORLEVEL_ERROR);
      }

      $this->recursiveSelfReferenceField = $this->getModelfieldInstance($selfReferenceField);
      $this->recursiveAnchorField = $this->getModelfieldInstance($anchorField);

      foreach($anchorConditions as $cond) {
        if($cond instanceof \codename\core\model\plugin\filter) {
          $this->recursiveAnchorConditions[] = $cond;
        } else {
          $this->recursiveAnchorConditions[] = $this->createFilterPluginInstance($cond);
        }
      }

      return $this;
    }

    /**
     * [createFilterPluginInstance description]
     * @param  array                               $data [description]
     * @return \codename\core\model\plugin\filter        [description]
     */
    protected function createFilterPluginInstance(array $data): \codename\core\model\plugin\filter {
      $field = $data['field'];
      $value = $data['value'];
      $operator = $data['operator'];
      $conjunction = $data['conjunction'] ?? null;
      $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
      if(\is_array($value)) {
        if(\count($value) === 0) {
            throw new exception('EXCEPTION_MODEL_CREATEFILTERPLUGININSTANCE_INVALID_VALUE', exception::$ERRORLEVEL_ERROR);
        }
        return new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
      } else {
        $modelfieldInstance = $this->getModelfieldInstance($field);
        return new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
      }
    }

    /**
     * [protected description]
     * @var bool
     */
    protected $recursive = false;

    /**
     * [protected description]
     * @var \codename\core\value\text\modelfield|null
     */
    protected $recursiveSelfReferenceField = null;

    /**
     * [protected description]
     * @var \codename\core\value\text\modelfield|null
     */
    protected $recursiveAnchorField = null;

    /**
     * [protected description]
     * @var \codename\core\model\plugin\filter[]
     */
    protected $recursiveAnchorConditions = [];

    /**
     * contains configured join plugin instances for nested models
     * @var \codename\core\model\plugin\join[]
     */
    protected $nestedModels = array();

    /**
     * I load an entry of the given model identified by the $primarykey to the current instance.
     * @param string $primaryKey
     * @return \codename\core\model
     */
    public function entryLoad(string $primaryKey) : \codename\core\model {
        $entry = $this->loadByUnique($this->getPrimarykey(), $primaryKey);
        if(empty($entry)) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYLOAD_FAILED, \codename\core\exception::$ERRORLEVEL_FATAL);
        }
        $this->entryMake($entry);
        return $this;
    }

    /**
     * I save the currently loaded entry to the model storage
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entrySave() : \codename\core\model {
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYSAVE_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL);
        }
        $this->saveWithChildren($this->data->getData());
        return $this;
    }

    /**
     * I will overwrite the fields of my model using the $data array
     * @param array $data
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entryUpdate(array $data) : \codename\core\model {
        if(count($data) == 0) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        foreach($this->getFields() as $field) {
            if(array_key_exists($field, $data)) {
                $this->fieldSet($this->getModelfieldInstance($field), $data[$field]);
            }
        }
        return $this;
    }

    /**
     * I set a flag (identified by the integer $flagval) to TRUE.
     * @param int $flagval
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entrySetflag(int $flagval) : \codename\core\model {
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if(!$this->config->exists('flag')) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if($flagval < 0) {
          // Only allow >= 0
          throw new \codename\core\exception(self::EXCEPTION_INVALID_FLAG_VALUE, \codename\core\exception::$ERRORLEVEL_ERROR, $flagval);
        }

        $flag = $this->fieldGet($this->getModelfieldInstance($this->table . '_flag'));
        $flag |= $flagval;
        $this->fieldSet($this->getModelfieldInstance($this->table . '_flag'), $flag);
        return $this;
    }

    /**
     * I set a flag (identified by the integer $flagval) to FALSE.
     * @param int $flagval
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entryUnsetflag(int $flagval) : \codename\core\model {
        if($this->data === null || empty($this->data->getData())) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if(!$this->config->exists('flag')) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if($flagval < 0) {
          // Only allow >= 0
          throw new \codename\core\exception(self::EXCEPTION_INVALID_FLAG_VALUE, \codename\core\exception::$ERRORLEVEL_ERROR, $flagval);
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
     */
    public function flagfieldValue(int $flag = 0, array $flagSettings) : int {
        if(!$this->config->exists('flag')) {
            throw new \codename\core\exception(self::EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        $flags = $this->config->get('flag');
        $validFlagValues = array_values($flags);
        foreach($flagSettings as $flagval => $state) {
          if(in_array($flagval, $validFlagValues)) {
            if($state === true) {
              $flag |= $flagval;
            } else if($state === false) {
              $flag &= ~$flagval;
            } else {
              // do nothing!
            }
          }
        }
        return $flag;
    }

    /**
     * Creates an instance
     * @param array $modeldata
     * @return model
     * @todo refactor the constructor for no method args
     */
    public function __CONSTRUCT(array $modeldata = array()) {
        $this->errorstack = new \codename\core\errorstack('VALIDATION');
        $this->modeldata = new \codename\core\config($modeldata);
        return $this;
    }

    /**
     * Initiates a servicing instance for this model
     * @return void
     */
    protected function initServicingInstance() {
      // no implementation for base model
    }

    /**
     * [protected description]
     * @var model\servicing
     */
    protected $servicingInstance = null;

    /**
     * model data passed during initialization
     * @var \codename\core\config
     */
    protected $modeldata = null;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::load($primaryKey)
     */
    public function load($primaryKey) : array {
        return (is_null($primaryKey) ? array() : $this->loadByUnique($this->getPrimarykey(), $primaryKey));
    }

    /**
     * Loads the given entry as well as the depending objects
     * @param string $primaryKey
     * @return array
     */
    public function loadAll(string $primaryKey) : array {

        if($this->config->exists("foreign")) {
            foreach($this->config->get("foreign") as $reference) {
                $model = app::getModel($reference['model']);
                if(get_class($model) !== get_class($this)) {
                    $this->addModel(app::getModel($reference['model']));
                }
            }
        }
        return $this->addFilter($this->getPrimarykey(), $primaryKey)->search()->getResult()[0];
    }

    /**
     * [addUseindex description]
     * @param  array $fields [description]
     * @return model         [description]
     */
    public function addUseIndex(array $fields) : model {
      throw new \LogicException('Not implemented for this kind of model');
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addFilter($field, $value, $operator)
     */
    public function addFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if(\is_array($value)) {
            if(\count($value) === 0) {
                trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
                return $this;
            }
            \array_push($this->filter, new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction));
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            \array_push($this->filter, new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction));
        }
        return $this;
    }

    /**
     * add a custom filter plugin
     * @param  \codename\core\model\plugin\filter $filterPlugin [description]
     * @return model                                            [description]
     */
    public function addFilterPlugin(\codename\core\model\plugin\filter $filterPlugin) : model {
        array_push($this->filter, $filterPlugin);
        return $this;
    }


    /**
     * [addFilterPluginCollection description]
     * @param  \codename\core\model\plugin\filter\filterInterface[]   $filterPlugins [array of filter plugin instances]
     * @param  string                                 $groupOperator [operator to be used between all collection items]
     * @param  string                                 $groupName     [filter group name]
     * @param  string|null                            $conjunction   [conjunction to be used inside a filter group]
     * @return model
     */
    public function addFilterPluginCollection(array $filterPlugins, string $groupOperator = 'AND', string $groupName = 'default', string $conjunction = null) : model {
      $filterCollection = array();
      foreach($filterPlugins as $filter) {
        if($filter instanceof \codename\core\model\plugin\filter\filterInterface
          || $filter instanceof \codename\core\model\plugin\managedFilterInterface) {
          $filterCollection[] = $filter;
        } else {
          throw new exception('MODEL_INVALID_FILTER_PLUGIN', exception::$ERRORLEVEL_ERROR);
        }
      }
      if(count($filterCollection) > 0) {
        $this->filterCollections[$groupName][] = array(
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction
        );
      }
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addFilterList($field, $value, $operator)
     */
    public function addFilterList(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
        $class = '\\codename\\core\\model\\plugin\\filterlist\\' . $this->getType();
        // NOTE: the value becomes into model\schematic\sql checked
        array_push($this->filter, new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction));
        return $this;
    }

    /**
     * [addAggregateFilter description]
     * @param  string               $field       [description]
     * @param  string|int|bool|null $value       [description]
     * @param  string $operator    [description]
     * @param  string|null          $conjunction [description]
     * @return model               [description]
     */
    public function addAggregateFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
      $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
      if(is_array($value)) {
          if(count($value) == 0) {
              trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
              return $this;
          }
          array_push($this->aggregateFilter, new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction));
      } else {
          $modelfieldInstance = $this->getModelfieldInstance($field);
          array_push($this->aggregateFilter, new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction));
      }
      return $this;
    }

    /**
     * [addDefaultAggregateFilter description]
     * @param  string               $field       [description]
     * @param  string|int|bool|null $value       [description]
     * @param  string $operator    [description]
     * @param  string|null          $conjunction [description]
     * @return model               [description]
     */
    public function addDefaultAggregateFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
      $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
      if(is_array($value)) {
          if(count($value) == 0) {
              trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
              return $this;
          }
          $instance = new $class($this->getModelfieldInstance($field), $value, $operator, $conjunction);
          array_push($this->aggregateFilter, $instance);
          array_push($this->defaultAggregateFilter, $instance);
      } else {
          $modelfieldInstance = $this->getModelfieldInstance($field);
          $instance = new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $conjunction);
          array_push($this->aggregateFilter, $instance);
          array_push($this->defaultAggregateFilter, $instance);
      }
      return $this;
    }

    /**
     * [addAggregateFilterPlugin description]
     * @param  \codename\core\model\plugin\aggregatefilter $filterPlugin [description]
     * @return model                                                [description]
     */
    public function addAggregateFilterPlugin(\codename\core\model\plugin\aggregatefilter $filterPlugin) : model {
      array_push($this->aggregateFilter, $filterPlugin);
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addFilter($field, $value, $operator)
     */
    public function addFieldFilter(string $field, string $otherField, string $operator = '=', string $conjunction = null) : model {
        $class = '\\codename\\core\\model\\plugin\\fieldfilter\\' . $this->getType();
        array_push($this->filter, new $class($this->getModelfieldInstance($field), $this->getModelfieldInstance($otherField), $operator, $conjunction));
        return $this;
    }


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
    protected $filterCollections = array();

    /**
     * Contains instances of the filters that will be used again after resetting the model
     * @var array
     */
    protected $defaultfilterCollections = array();

    /**
     * Adds a grouped collection of filters to the underlying filter collection
     * this is used for changing operators (AND/OR/...) and grouping several filters (where statements)
     * @TODO: make this better, could also use valueobjects?
     * @param array $filters [array of array( 'field' => ..., 'value' => ... )-elements]
     * @param string $groupOperator [e.g. 'AND' or 'OR']
     */
    public function addFilterCollection(array $filters, string $groupOperator = 'AND', string $groupName = 'default', string $conjunction = null) : model {
      $filterCollection = array();
      foreach($filters as $filter) {
        $field = $filter['field'];
        $value = $filter['value'];
        $operator = $filter['operator'];
        $filter_conjunction = $filter['conjunction'] ?? null;
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if(is_array($value)) {
            if(count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
                continue;
            }
            array_push($filterCollection, new $class($this->getModelfieldInstance($field), $value, $operator, $filter_conjunction));
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            array_push($filterCollection, new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $filter_conjunction));
        }
      }
      if(count($filterCollection) > 0) {
        $this->filterCollections[$groupName][] = array(
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction
        );
      }
      return $this;
    }

    /**
     * [addDefaultFilterCollection description]
     * @param  array        $filters                     [array of filters]
     * @param  string|null  $groupOperator               [operator inside the group items]
     * @param  string       $groupName                   [name of group to usage across models]
     * @param  string|null  $conjunction                 [conjunction of this group, inside the group of same-name filtercollections]
     * @return model                 [description]
     */
    public function addDefaultFilterCollection(array $filters, string $groupOperator = null, string $groupName = 'default', string $conjunction = null) : model {
      $filterCollection = array();
      foreach($filters as $filter) {
        $field = $filter['field'];
        $value = $filter['value'];
        $operator = $filter['operator'];
        $filter_conjunction = $filter['conjunction'] ?? null;
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if(is_array($value)) {
            if(count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
                continue;
            }
            array_push($filterCollection, new $class($this->getModelfieldInstance($field), $value, $operator, $filter_conjunction));
        } else {
            $modelfieldInstance = $this->getModelfieldInstance($field);
            array_push($filterCollection, new $class($modelfieldInstance, $this->delimitImproved($modelfieldInstance->get(), $value), $operator, $filter_conjunction));
        }
      }
      if(\count($filterCollection) > 0) {
        $this->defaultfilterCollections[$groupName][] = array(
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction
        );
        $this->filterCollections[$groupName][] = array(
              'operator' => $groupOperator,
              'filters' => $filterCollection,
              'conjunction' => $conjunction
        );
      }
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addDefaultfilter($field, $value, $operator)
     */
    public function addDefaultfilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
        $field = $this->getModelfieldInstance($field);
        // if(!$this->fieldExists($field)) {
        //     throw new \codename\core\exception(self::EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        // }
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();

        if(is_array($value)) {
            if(count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
                return $this;
            }
            $instance = new $class($field, $value, $operator, $conjunction);
            array_push($this->defaultfilter, $instance);
            array_push($this->filter, $instance);
        } else {
            $instance = new $class($field, $this->delimit($field, $value), $operator, $conjunction);
            array_push($this->defaultfilter, $instance);
            array_push($this->filter, $instance);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addDefaultfilter($field, $value, $operator)
     */
    public function addDefaultfilterlist(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
        $field = $this->getModelfieldInstance($field);
        // if(!$this->fieldExists($field)) {
        //     throw new \codename\core\exception(self::EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        // }
        $class = '\\codename\\core\\model\\plugin\\filterlist\\' . $this->getType();

        if(is_array($value)) {
            if(count($value) == 0) {
                trigger_error('Empty array filter values have no effect on resultset', E_USER_NOTICE);
                return $this;
            }
            $instance = new $class($field, $value, $operator, $conjunction);
            array_push($this->defaultfilter, $instance);
            array_push($this->filter, $instance);
        } else {
            $instance = new $class($field, $this->delimit($field, $value), $operator, $conjunction);
            array_push($this->defaultfilter, $instance);
            array_push($this->filter, $instance);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addOrder($field, $order)
     */
    public function addOrder(string $field, string $order = 'ASC') : model {
        $field = $this->getModelfieldInstanceRecursive($field) ?? $this->getModelfieldInstance($field);
        if(!$this->fieldExists($field)) {
            // check for existance of a calculated field!
            $found = false;
            foreach($this->fieldlist as $f) {
              if($f->field == $field) {
                $found = true;
                break;
              }
            }

            if(!$found) {
              throw new \codename\core\exception(self::EXCEPTION_ADDORDER_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
            }
        }
        $class = '\\codename\\core\\model\\plugin\\order\\' . $this->getType();
        array_push($this->order, new $class($field, $order));
        return $this;
    }

    /**
     * [addOrderPlugin description]
     * @param  \codename\core\model\plugin\order $orderPlugin [description]
     * @return model                                          [description]
     */
    public function addOrderPlugin(\codename\core\model\plugin\order $orderPlugin) : model {
      array_push($this->order, $orderPlugin);
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addField($field)
     */
    public function addField(string $field, string $alias = null) : model {
        if(strpos($field, ',') !== false) {
            if($alias) {
              // This is impossible, multiple fields and a singular alias.
              // We won't support (field1, field2), (alias1, alias2) in this method
              throw new exception('EXCEPTION_ADDFIELD_ALIAS_ON_MULTIPLE_FIELDS', exception::$ERRORLEVEL_ERROR, [ 'field' => $field, 'alias' => $alias ]);
            }
            foreach(explode(',', $field) as $myField) {
                $this->addField(trim($myField));
            }
            return $this;
        }

        $field = $this->getModelfieldInstance($field);
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_ADDFIELD_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
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
     * virtual field functions
     * @var callable[]
     */
    protected $virtualFields = [];

    /**
     * [addVirtualField description]
     * @param string   $field         [description]
     * @param callable $fieldFunction [description]
     * @return model [this instance]
     */
    public function addVirtualField(string $field, callable $fieldFunction) : model {
      $this->virtualFields[$field] = $fieldFunction;
      return $this;
    }

    /**
     * [handleVirtualFields description]
     * @param  array $dataset [description]
     * @return array          [description]
     */
    public function handleVirtualFields(array $dataset) : array {
      foreach($this->virtualFields as $field => $function) {
        $dataset[$field] = $function($dataset);
      }
      return $dataset;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::hideField($field)
     */
    public function hideField(string $field) : model {
        if(strpos($field, ',') !== false) {
            foreach(explode(',', $field) as $myField) {
                $this->hideField(trim($myField));
            }
            return $this;
        }
        $this->hiddenFields[] = $field;
        return $this;
    }

    /**
     * [hideAllFields description]
     * @return model [description]
     */
    public function hideAllFields() : model  {
      foreach($this->getFields() as $field) {
        $this->hideField($field);
      }
      return $this;
    }

    /**
     * groupBy fields
     * @var \codename\core\model\plugin\group[]
     */
    protected $group = array();

    /**
     * @inheritDoc
     */
    public function addGroup(string $field) : model {
      $field = $this->getModelfieldInstance($field);
      $aliased = false;
      if(!$this->fieldExists($field)) {
        $foundInFieldlist = false;
        foreach($this->fieldlist as $checkField) {
          if($checkField->field->get() == $field->get()) {
            $foundInFieldlist = true;

            // At this point, check for 'virtuality' of a field
            // e.g. aliased, calculated and aggregates
            // (the latter ones are usually calculated fields)
            //
            if($checkField instanceof \codename\core\model\plugin\calculatedfield
              || $checkField instanceof \codename\core\model\plugin\aggregate) {
              $aliased = true;
            }
            break;
          }
        }
        if($foundInFieldlist === false) {
          throw new \codename\core\exception(self::EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST, \codename\core\exception::$ERRORLEVEL_FATAL, array($field, $this->fieldlist));
        }
      }
      $class = '\\codename\\core\\model\\plugin\\group\\' . $this->getType();
      $groupInstance = new $class($field);
      $groupInstance->aliased = $aliased;
      $this->group[] = $groupInstance;
      return $this;
    }

    /**
     * exception thrown when trying to add a nonexisting field to grouping parameters
     * @var string
     */
    const EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST = "EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST";

    /**
     * @inheritDoc
     */
    public function addCalculatedField(string $field, string $calculation) : model {
      $field = $this->getModelfieldInstance($field);
      // only check for EXISTANCE of the fieldname, cancel if so - we don't want duplicates!
      if($this->fieldExists($field)) {
        throw new \codename\core\exception(self::EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
      }
      $class = '\\codename\\core\\model\\plugin\\calculatedfield\\' . $this->getType();
      $this->fieldlist[] = new $class($field, $calculation);
      return $this;
    }

    /**
     * [removeCalculatedField description]
     * @param  string $field [description]
     * @return model         [description]
     */
    public function removeCalculatedField(string $field) : model {
      $field = $this->getModelfieldInstance($field);
      $this->fieldlist = array_filter($this->fieldlist, function($item) use ($field) {
        if($item instanceof \codename\core\model\plugin\calculatedfield) {
          if($item->field->get() == $field->get()) {
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
     * @param  string $field           [description]
     * @param  string $calculationType [description]
     * @param  string $fieldBase       [description]
     * @return model                   [description]
     */
    public function addAggregateField(string $field, string $calculationType, string $fieldBase) : model {
      $field = $this->getModelfieldInstance($field);
      $fieldBase = $this->getModelfieldInstance($fieldBase);
      // only check for EXISTANCE of the fieldname, cancel if so - we don't want duplicates!
      if($this->fieldExists($field)) {
        throw new \codename\core\exception(self::EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
      }
      $class = '\\codename\\core\\model\\plugin\\aggregate\\' . $this->getType();
      $this->fieldlist[] = new $class($field, $calculationType, $fieldBase);
      return $this;
    }

    /**
     * [addFulltextField description]
     * @param  string $field  [description]
     * @param  string $value  [description]
     * @param  string $fields [description]
     * @return model          [description]
     */
    public function addFulltextField(string $field, string $value, $fields) : model {
      $field = $this->getModelfieldInstance($field);
      if(!is_array($fields)) {
        $fields = explode(',', $fields);
      }
      if (count($fields) === 0) {
        throw new \codename\core\exception(self::EXCEPTION_ADDFULLTEXTFIELD_NO_FIELDS_FOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $fields);
      }
      $thisFields = [];
      foreach($fields as $resultField) {
        $thisFields[] = $this->getModelfieldInstance(trim($resultField));
      }
      $class = '\\codename\\core\\model\\plugin\\fulltext\\' . $this->getType();
      $this->fieldlist[] = new $class($field, $value, $thisFields);
      return $this;
    }

    /**
     * exception thrown on duplicate field existance (during addition of an aggregated field)
     * @var string
     */
    const EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS = 'EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS';

    /**
     * exception thrown if we try to add a calculated field which already exists (either as db field or another calculated one)
     * @var string
     */
    const EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS = "EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS";

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::setLimit($limit)
     */
    public function setLimit(int $limit) : model {
        $class = '\\codename\\core\\model\\plugin\\limit\\' . $this->getType();
        $this->limit = new $class($limit);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::setOffset($offset)
     */
    public function setOffset(int $offset) : model {
        $class = '\\codename\\core\\model\\plugin\\offset\\' . $this->getType();
        $this->offset = new $class($offset);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilterDuplicates(bool $state) : \codename\core\model {
        $this->filterDuplicates = $state;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::setCache($cache)
     */
    public function useCache() : model {
        $this->cache = true;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::loadByUnique($field, $primaryKey)
     */
    public function loadByUnique(string $field, string $value) : array {
        $data = $this->addFilter($field, $value, '=')->setLimit(1);

        // the primary key based cache should ONLY be active, if we're querying only this model
        // without joins and only with a filter on the primary key
        //
        // if($field == $this->getPrimarykey() && count($this->filter) === 1 && count($this->filterCollections) === 0 && count($this->getNestedJoins()) === 0) {
        //     $cacheObj = app::getCache();
        //     $cacheGroup = $this->getCachegroup();
        //     $cacheKey = "PRIMARY_" . $value;
        //
        //     $myData = $cacheObj->get($cacheGroup, $cacheKey);
        //
        //     if(!is_array($myData) || count($myData) === 0) {
        //         $myData = $data->search()->getResult();
        //         if(count($myData) == 1) {
        //             $cacheObj->set($cacheGroup, $cacheKey, $myData);
        //         }
        //     } else {
        //       // REVIEW:
        //       // We reset() the model here, as the filter created previously
        //       // may be passed to the next query...
        //       $data->reset();
        //     }
        //
        //     if(count($myData) === 1) {
        //         return $myData[0];
        //     } else if(count($myData) > 1) {
        //         throw new \codename\core\exception('EXCEPTION_MODEL_LOADBYUNIQUE_MULTIPLE_RESULTS', exception::$ERRORLEVEL_FATAL, $myData);
        //     }
        //     return array();
        // }
        //
        // $data->useCache();

        $data = $data->search()->getResult();
        if(count($data) == 0) {
            return array();
        }
        return $data[0];
    }

    /**
     * [getFieldtypeImproved description]
     * @param  string $specifier [description]
     * @return string|null
     */
    public function getFieldtypeImproved(string $specifier) : ?string {
      if(isset($this->cachedFieldtype[$specifier])) {
        return $this->cachedFieldtype[$specifier];
      } else {

        // // DEBUG
        // \codename\core\app::getResponse()->setData('getFieldtypeCounter', \codename\core\app::getResponse()->getData('getFieldtypeCounter') +1 );

        // fieldtype not in current model config
        if(($fieldtype = $this->config->get("datatype>" . $specifier))) {

          // field in this model
          $this->cachedFieldtype[$specifier] = $fieldtype;
          return $this->cachedFieldtype[$specifier];

        } else {

          // check nested model configs
          foreach($this->nestedModels as $joinPlugin) {
            $fieldtype = $joinPlugin->model->getFieldtypeImproved($specifier);
            if($fieldtype !== null) {
              $this->cachedFieldtype[$specifier] = $fieldtype;
              return $fieldtype;
            }
          }

          // cache error value, too
          $fieldtype = null;

          // // DEBUG
          // \codename\core\app::getResponse()->setData('fieldtype_errors', array_merge(\codename\core\app::getResponse()->getData('fieldtype_errors') ?? [], [ $this->getIdentifier().':'.$specifier ]) );

          $this->cachedFieldtype[$specifier] = $fieldtype;
          return $this->cachedFieldtype[$specifier];
        }

      }
    }

    /**
     * Returns the datatype of the given field
     * @param \codename\core\value\text\modelfield $field
     * @return string
     */
    public function getFieldtype(\codename\core\value\text\modelfield $field) {
      $specifier = $field->get();
      if(isset($this->cachedFieldtype[$specifier])) {
        return $this->cachedFieldtype[$specifier];
      } else {

        // // DEBUG
        // \codename\core\app::getResponse()->setData('getFieldtypeCounter', \codename\core\app::getResponse()->getData('getFieldtypeCounter') +1 );

        // fieldtype not in current model config
        if(($fieldtype = $this->config->get("datatype>" . $specifier))) {

          // field in this model
          $this->cachedFieldtype[$specifier] = $fieldtype;
          return $this->cachedFieldtype[$specifier];

        } else {

          // check nested model configs
          foreach($this->nestedModels as $joinPlugin) {
            $fieldtype = $joinPlugin->model->getFieldtype($field);
            if($fieldtype !== null) {
              $this->cachedFieldtype[$specifier] = $fieldtype;
              return $fieldtype;
            }
          }

          // cache error value, too
          $fieldtype = null;

          // // DEBUG
          // \codename\core\app::getResponse()->setData('fieldtype_errors', array_merge(\codename\core\app::getResponse()->getData('fieldtype_errors') ?? [], [ $this->getIdentifier().':'.$specifier ]) );

          $this->cachedFieldtype[$specifier] = $fieldtype;
          return $this->cachedFieldtype[$specifier];
        }

      }
    }

    /**
     * internal in-mem caching of fieldtypes
     * @var array
     */
    protected $cachedFieldtype = array();

    /**
     * Returns array of fields that exist in the model
     * @return array
     */
    public function getFields() : array {
        return $this->config->get('field');
    }

    /**
     * [getFieldlistArray description]
     * @param  \codename\core\model\plugin\field[] $fields [description]
     * @return array         [description]
     */
    protected function getFieldlistArray(array $fields) : array {
      $returnFields = [];
      if(count($fields) > 0) {
        foreach($fields as $field) {
          if($field->alias ?? false) {
            $returnFields[] = $field->alias->get();
          } else {
            $returnFields[] = $field->field->get();
          }
        }
      }

      // filter out hidden fields by difference calculation
      $returnFields = array_diff($returnFields, $this->hiddenFields);

      return $returnFields;
    }

    /**
     * Returns a list of fields (as strings)
     * that are part of the current model
     * or were added dynamically (aliased, function-based or implicit)
     * and handles hidden fields, too.
     *
     * By the way, virtual fields are *not* returned by this function
     * As the framework defines virtual fields as only to be existant
     * if the corresponding join is also used.
     *
     * @return string[]
     */
    public function getCurrentAliasedFieldlist() : array {
      $result = array();
      if(\count($this->fieldlist) == 0 && \count($this->hiddenFields) > 0) {
        //
        // Include all fields but specific ones
        //
        foreach($this->getFields() as $fieldName) {
          if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
            if(!in_array($fieldName, $this->hiddenFields)) {
              $result[] = $fieldName;
            }
          }
        }
      } else {
        if(count($this->fieldlist) > 0) {
          //
          // Explicit field list
          //
          foreach($this->fieldlist as $field) {
            if($field instanceof \codename\core\model\plugin\calculatedfield\calculatedfieldInterface) {
              //
              // Get the field's alias
              //
              $result[] = $field->field->get();
            } else if($field instanceof \codename\core\model\plugin\aggregate\aggregateInterface) {
              //
              // aggregate field's alias
              //
              $result[] = $field->field->get();
            } else if($field instanceof \codename\core\model\plugin\fulltext\fulltextInterface) {

              //
              // fulltext field's alias
              //
              $result[] = $field->field->get();
            } else if($this->config->get('datatype>'.$field->field->get()) !== 'virtual' && (!in_array($field->field->get(), $this->hiddenFields) || $field->alias)) {

              //
              // omit virtual fields
              // they're not part of the DB.
              //
              $fieldAlias = $field->alias !== null ? $field->alias->get() : null;
              if($fieldAlias) {
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
          foreach($this->getFields() as $fieldName) {
            if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
              if(!in_array($fieldName, $this->hiddenFields)) {
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

        } else {
          //
          // No explicit fieldlist
          // No explicit hidden fields
          //
          if(count($this->hiddenFields) === 0) {
            //
            // The rest of the fields. Skip virtual fields
            //
            foreach($this->getFields() as $fieldName) {
              if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
                $result[] = $fieldName;
              }
            }
          } else {
            // ugh?
          }
        }

      }

      return array_values($result);
    }

    /**
    * Enables virtual field result functionality on this model instance
    * @param  bool                 $state [description]
    * @return \codename\core\model        [description]
    */
    public function setVirtualFieldResult(bool $state) : \codename\core\model {
      return $this;
    }

    /**
     * returns an array of virtual fields (names) currently configured
     * @return array [description]
     */
    public function getVirtualFields() : array {
        return array_keys($this->virtualFields);
    }

    /**
     * primarykey cache field
     * @var string
     */
    protected $primarykey = null;

    /**
     * Returns the primary key that was configured in the model's JSON config
     * @return string
     */
    public function getPrimarykey() : string {
        if($this->primarykey === null) {
          if(!$this->config->exists("primary")) {
              throw new \codename\core\exception(self::EXCEPTION_GETPRIMARYKEY_NOPRIMARYKEYINCONFIG, \codename\core\exception::$ERRORLEVEL_FATAL, $this->config->get());
          }
          $this->primarykey = $this->config->get('primary')[0];
        }
        return $this->primarykey;
    }

    /**
     * Validates the data array and returns true if no errors occured
     * @param array $data
     * @return bool
     */
    public function isValid(array $data) : bool {
        return (count($this->validate($data)->getErrors()) == 0);
    }

    /**
     * Returns the errors of the errorstack in this instance
     * @return array
     */
    public function getErrors() : array {
        return $this->errorstack->getErrors();
    }

    /**
     * Validates the given data after normalizing it.
     * @param array $data
     * @return model
     * @todo requred seems to have some bugs
     * @todo bring back to life the UNIQUE constraint checker
     * @todo move the UNIQUE constraint checks to a separaate method
     */
    public function validate(array $data) : model {

        //
        // CHANGED 2020-07-29 reset the current errorstack just right before validation
        //
        $this->errorstack->reset();

        foreach($this->config->get('field') as $field) {
            if(in_array($field, array($this->getPrimarykey(), $this->getIdentifier() . "_modified", $this->getIdentifier() . "_created"))) {
                continue;
            }
            if (!array_key_exists($field, $data) || is_null($data[$field]) || (is_string($data[$field]) && strlen($data[$field]) == 0) ) {
                if(is_array($this->config->get('required')) && in_array($field, $this->config->get("required"))) {
                    $this->errorstack->addError($field, 'FIELD_IS_REQUIRED', null);
                }
                continue;
            }

            if($this->config->exists('children') && $this->config->exists('children>'.$field)) {
              // validate child using child/nested model
              $childConfig = $this->config->get('children>'.$field);

              if($childConfig['type'] === 'foreign') {
                //
                // Normal Foreign-Key based child (1:1)
                //
                $foreignConfig = $this->config->get('foreign>'.$childConfig['field']);
                $foreignKeyField = $childConfig['field'];

                // get the join plugin valid for the child reference field
                $res = $this->getNestedJoins($foreignConfig['model'], $childConfig['field']);

                if(count($res) === 1) {
                  $join = $res[0]; // reset($res);
                  $join->model->validate($data[$field]);
                  if(count($errors = $join->model->getErrors()) > 0) {
                    $this->errorstack->addError($field, 'FIELD_INVALID', $errors);
                  }
                } else {
                  continue;
                }
              } else if($childConfig['type'] === 'collection') {
                //
                // Collections in a virtual field
                //
                $collectionConfig = $this->config->get('collection>'.$field);

                // TODO: get the corresponding model
                // we might introduce a new "addCollectionModel" method or so

                if(isset($this->collectionPlugins[$field])) {
                  if(is_array($data[$field])) {
                    foreach($data[$field] as $collectionItem) {
                      $this->collectionPlugins[$field]->collectionModel->validate($collectionItem);
                      if(count($errors = $this->collectionPlugins[$field]->collectionModel->getErrors()) > 0) {
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
        if($this->config->exists('validators')) {
          $validators = $this->config->get('validators');
          foreach($validators as $validator) {
            // NOTE: reset validator needed, as app::getValidator() caches the validator instance,
            // including the current errorstack
            if(count($errors = app::getValidator($validator)->reset()->validate($data)) > 0) {
              //
              // NOTE/CHANGED 2020-02-18
              // split errors into field-related and others
              // to improve validation handling
              //
              $dataErrors = [];
              $fieldErrors = [];
              foreach($errors as $error) {
                if(in_array($error['__IDENTIFIER'], $this->getFields())) {
                  $fieldErrors[] = $error;
                } else {
                  $dataErrors[] = $error;
                }
              }
              if(count($dataErrors) > 0) {
                $this->errorstack->addError('DATA', 'INVALID', $dataErrors);
              }
              if(count($fieldErrors) > 0) {
                $this->errorstack->addErrors($fieldErrors);
              }
            }
          }
        }

        // $dataob = $this->data;
        // if(is_array($this->config->get("unique"))) {
        //     foreach($this->config->get("unique") as $key => $fields) {
        //         if(!is_array($fields)) {
        //             continue;
        //         }
        //         $filtersApplied = 0;
        //
        //         // exclude my own dataset if UPDATE is in progress
        //         if(array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0) {
        //             $this->addFilter($this->getPrimarykey(), $data[$this->getPrimarykey()], '!=');
        //         }
        //
        //         foreach($fields as $field) {
        //             // if(!array_key_exists($field, $data) || strlen($data[$field]) == 0) {
        //             //     continue;
        //             // }
        //             if(is_array($field)) {
        //               // $this->addFilter($field, $data[$field] ?? null, '=');
        //               $uniqueFilters = [];
        //               foreach($field as $uniqueFieldComponent) {
        //                 $uniqueFilters[] = [ 'field' => $uniqueFieldComponent, 'value' => $data[$uniqueFieldComponent], 'operator' => '='];
        //                 if($data[$uniqueFieldComponent] === null) {
        //                   break;
        //                 }
        //               }
        //               $this->addFilterCollection($uniqueFilters, 'AND');
        //             } else {
        //               $this->addFilter($field, $data[$field] ?? null, '=');
        //             }
        //             $filtersApplied++;
        //         }
        //
        //         if($filtersApplied === 0) {
        //             continue;
        //         }
        //
        //         if(count($this->search()->getResult()) > 0) {
        //             $this->errorstack->addError($field, 'FIELD_DUPLICATE', $data[$field]);
        //         }
        //     }
        // }
        // $this->data = $dataob;

        return $this;
    }


    /**
     * internal caching variable containing the list of fields in the model
     * @var array
     */
    protected $normalizeDataFieldCache = null;

    /**
     * normalizes data in the given array.
     * <br />Tries to identify complex datastructures by the Hiden $FIELDNAME."_" fields and makes objects of them
     * @param array $data
     * @return array
     */
    public function normalizeData(array $data) : array {
        $myData = array();

        $flagFieldName = $this->table . '_flag';

        if(!$this->normalizeDataFieldCache) {
          $this->normalizeDataFieldCache = $this->getFields();
        }

        foreach($this->normalizeDataFieldCache as $field) {
            // if field has object identified
            //
            // OBSOLETE, possibly. From the old days.
            //
            // if(array_key_exists($field.'_', $data)) {
            //     $object = array();
            //     foreach($data as $key => $value) {
            //         if(strpos($key, $field.'__') !== false) {
            //             $object[str_replace($field . '__', '', strtolower($key))] = $data[$key];
            //         }
            //     }
            //     $myData[$field] = $object;
            // }

            if($field == $flagFieldName) {
                if(array_key_exists($flagFieldName, $data)) {
                    if(!is_array($data[$flagFieldName])) {
                        //
                        // CHANGED 2021-09-21: flag field values may be passed-through
                        // if not in array-format
                        // TODO: validate flags?
                        //
                        $myData[$field] = $data[$flagFieldName];
                        continue;
                    }

                    $flagval = 0;
                    foreach($data[$flagFieldName] as $flagname => $status) {
                        $currflag = $this->config->get("flag>$flagname");
                        if(is_null($currflag) || !$status) {
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
            if(\array_key_exists($field, $data)) {
                // $myData[$field] = $this->importField($this->getModelfieldInstance($field), $data[$field]);
                $myData[$field] = $this->importFieldImproved($field, $data[$field]);
            }

        }
        return $myData;
    }

    /**
     * Returns the given $flagname's flag integer value
     * @param string $flagname
     * @throws \codename\core\exception
     * @return NULL|\codename\core\multitype
     * @deprecated
     */
    public function getFlag(string $flagname) {
        if(!$this->config->exists("flag>$flagname")) {
            throw new \codename\core\exception(self::EXCEPTION_GETFLAG_FLAGNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $flagname);
            return null;
        }
        return $this->config->get("flag>$flagname");
    }

    /**
     * Returns true if the given flag name is set to true in the data array. Returns false otherwise
     * @param string $flagname
     * @param array $data
     * @return bool
     * @todo Validate the flag in the model constructor (model configurator)
     * @todo add \codename\core\exceptions
     */
    public function isFlag(int $flagvalue, array $data) : bool {
        $flagField = $this->getIdentifier() . '_flag';
        if(!array_key_exists($flagField, $data)) {
            throw new \codename\core\exception(self::EXCEPTION_ISFLAG_NOFLAGFIELD, \codename\core\exception::$ERRORLEVEL_FATAL, array('field' => $flagField, 'data' => $data));
        }
        return (($data[$flagField] & $flagvalue) == $flagvalue);
    }

    /**
     * Converts the storage format into a human readible format
     * @param \codename\core\value\text\modelfield $field
     * @param unknown $value
     * @return multitype
     */
    public function exportField(\codename\core\value\text\modelfield $field, $value = null) {
        if(is_null($value)) {
            return $value;
        }

        switch($this->getFieldtype($field)) {
            case 'boolean' :
                return $value === null ? null : ($value ? true : false); //  ? 'true' : 'false';
                break;
            case 'text_date':
                return date('Y-m-d', strtotime($value));
                break;
            case 'text' :
                return $value; // str_replace('#__DELIMITER__#', $this->delimiter, $value);
                break;
        }

        return $value;
    }

    /**
     * Returns true if the given $field exists in this model's configuration
     * @param \codename\core\value\text\modelfield $field
     * @return bool
     */
    protected function fieldExists(\codename\core\value\text\modelfield $field) : bool {
      if($field->getTable() != null) {
        if($field->getTable() == $this->table) {
          return in_array($field->get(), $this->getFields());
        } else {
          foreach($this->getNestedJoins() as $join) {
            if($join->model->fieldExists($field)) {
              return true;
            }
          }
        }
      }
      return in_array($field->get(), $this->getFields());
    }

    /**
     * Returns the default cache client
     * @return cache
     */
    protected function getCache() : cache {
        return app::getCache();
    }

    /**
     * [getCurrentCacheIdentifierParameters description]
     * @return array [description]
     */
    protected function getCurrentCacheIdentifierParameters() : array {
      $params = [];
      $params['filter'] = $this->filter;
      $params['filtercollections'] = $this->filterCollections;
      foreach($this->getNestedJoins() as $join) {
        //
        // CHANGED 2021-09-24: nested model's join plugin parameters were not correctly incorporated into cache key
        //
        $params['nest'][] = [
          'cacheIdentifier' => $join->model->getCurrentCacheIdentifierParameters(),
          'model'           => $join->model->getIdentifier(),
          'cacheParamters'  => $join->getCurrentCacheIdentifierParameters(),
        ];
      }
      return $params;
    }

    /**
     * Perform the given query and save the result in the instance
     * @param string $query
     * @return void
     */
    protected function doQuery(string $query, array $params = array()) {
        // if cache, load it
        if($this->cache) {
            $cacheObj = app::getCache();
            $cacheGroup = $this->getCachegroup();
            $cacheKey = "manualcache" . md5(serialize(
              array(
                get_class($this),
                $this->getIdentifier(),
                $query,
                $this->getCurrentCacheIdentifierParameters(),
                $params
              )
            ));

            // \codename\core\app::getResponse()->setData('cache_params', array(
            //   get_class($this),
            //   $query,
            //   $this->getCurrentCacheIdentifierParameters(),
            //   $params
            // ));

            $this->result = $cacheObj->get($cacheGroup, $cacheKey);

            if (!is_null($this->result) && is_array($this->result)) {
                $this->reset();
                return $this;
            }
        }

        $this->result = $this->internalQuery($query, $params);

        // save last query
        if($this->saveLastQuery) {
          $this->lastQuery = $query;
        }

        // if cache, save it
        if ($this->cache && count($this->getResult()) > 0) {
            $result = $this->getResult();

            $cacheObj->set($cacheGroup, $cacheKey, $this->getResult());
        }
        $this->reset();
        return;
    }

    /**
     * @inheritDoc
     */
     public function getResult() : array {
         $result = $this->result;

         if ($result === null) {
             $this->result = $this->internalGetResult();
             $result = $this->result;
         }

         // execute any bare joins, if set
         $result = $this->performBareJoin($result);

         $result = $this->normalizeResult($result);
         $this->data = new \codename\core\datacontainer($result);
         return $this->data->getData();
     }

    /**
     * perform a shim / bare metal join
     * @param array $result [the resultset]
     * @return array
     */
    protected function performBareJoin(array $result) : array {
      if(\count($this->getNestedJoins()) == 0) {
        return $result;
      }

      //
      // Loop through Joins
      //
      foreach($this->getNestedJoins() as $join) {
        $nest = $join->model;

        $vKey = null;
        if($this instanceof \codename\core\model\virtualFieldResultInterface && $this->virtualFieldResult) {
          // pick only parts of the arrays
          // if(($children = $this->config->get('children')) !== null) {
          //   foreach($children as $vField => $config) {
          //     if($config['type'] === 'foreign' && $config['field'] === $join->modelField) {
          //       $vKey = $vField;
          //     }
          //   }
          // }
          $vKey = $join->virtualField;
        }

        // virtual field?
        if($vKey && !$nest->getForceVirtualJoin()) {
          //
          // NOTE/CHANGED 2020-09-15 Forced virtual joins
          // require us to skip performBareJoin at this point in general
          // (for both vkey and non-vkey joins)
          //

          //
          // Skip recursive performBareJoin
          // if we have none coming up next
          //
          if(count($nest->getNestedJoins()) == 0) {
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

          if(!array_key_exists($vKey, $ifl)) {
            throw new exception('EXCEPTION_MODEL_PERFORMBAREJOIN_MISSING_VKEY', exception::$ERRORLEVEL_ERROR, [
              'model' => $this->getIdentifier(),
              'vKey'  => $vKey,
            ]);
          }

          //
          // Unwind resultset
          // [ item, item, item ] -> [ item[key], item[key], item[key] ]
          //
          $tResult = array_map(function($r) use ($vKey) {
            return $r[$vKey];
          }, $result);

          //
          // Recursively check for bareJoinable models
          // with a subset of the current result
          //
          $tResult = $nest->performBareJoin($tResult);

          //
          // Re-wind resultset
          // [ item[key], item[key], item[key] ] -> merge into [ item, item, item ]
          //
          foreach($result as $index => &$r) {
            $r[$vKey] = array_merge( $r[$vKey], $tResult[$index]);
          }
        } else if(!$nest->getForceVirtualJoin()) {
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
        if(!$this->compatibleJoin($nest) && ($join instanceof \codename\core\model\plugin\join\executableJoinInterface)) {

          $subresult = $nest->search()->getResult();

          if($vKey) {
            //
            // Unwind resultset
            // [ item, item, item ] -> [ item[key], item[key], item[key] ]
            //
            $tResult = array_map(function($r) use ($vKey) {
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
            foreach($result as $index => &$r) {
              $r[$vKey] = array_merge( $tResult[$index] );
            }
          } else {
            $result = $join->join($result, $subresult);
          }
        } else if(!$this->compatibleJoin($nest) && ($join instanceof \codename\core\model\plugin\join\dynamicJoinInterface)) {

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
     * internal query
     */
    protected abstract function internalQuery(string $query, array $params = array());

    /**
     * internal getResult
     * @return array
     */
    protected abstract function internalGetResult() : array;

    /**
     * determines query storing state
     * @var bool
     */
    public $saveLastQuery = false;

    /**
     * contains the last query performed with this model instance
     */
    protected $lastQuery = '';

    /**
     * returns the last query performed and stored.
     * @return string
     */
    public function getLastQuery() : string {
      return $this->lastQuery;
    }

    /**
     * Returns the lastInsertId returned from db driver
     * May contain foreign ids.
     */
    public function lastInsertId() {
      return $this->db->lastInsertId();
    }

    /**
     * Normalizes a result. Nests normalizeRow when more than one single row is in the result.
     * @param array $result
     * @return array
     */
    protected function normalizeResult(array $result) : array {
        if(count($result) == 0) {
            return array();
        }

        //
        // CHANGED 2020-05-13 - major change
        // we're no longer resetting normalizeModelFieldCache & normalizeModelFieldTypeCache
        // as it was reset every time we called normalizeResult.
        //
        // $this->normalizeModelFieldCache = array();
        // $this->normalizeModelFieldTypeCache = array();

        // Normalize single row
        if(count($result) == 1) {
            $result = reset($result);
            return array($this->normalizeRow($result));
        }

        // Normalize each row
        foreach($result as $key => $value) {
            $result[$key] = $this->normalizeRow($value);
        }
        return $result;
    }

    /**
     * Temporary model field cache during normalizeResult / normalizeRow
     * This is being reset each time normalizeResult is going to call normalizeRow
     * @author Kevin Dargel
     * @var \codename\core\value\text\modelfield[]
     */
    protected $normalizeModelFieldCache = array();

    /**
     * Temporary model field type cache during normalizeResult / normalizeRow
     * This is being reset each time normalizeResult is going to call normalizeRow
     * @author Kevin Dargel
     * @var \codename\core\value\text\modelfield[]
     */
    protected $normalizeModelFieldTypeCache = array();

    /**
     * [protected description]
     * @var bool[]
     */
    protected $normalizeModelFieldTypeStructureCache = array();

    /**
     * [protected description]
     * @var bool[]
     */
    protected $normalizeModelFieldTypeVirtualCache = array();

    /**
     * [getModelfieldInstance description]
     * @param  string                            $field [description]
     * @return \codename\core\value\text\modelfield        [description]
     */
    protected function getModelfieldInstance(string $field): \codename\core\value\text\modelfield {
      return \codename\core\value\text\modelfield::getInstance($field);
    }

    /**
     * Returns a modelfield instance or null
     * by traversing the current nested join tree
     * and identifying the correct schema and table
     *
     * @param  string                               $field [description]
     * @return \codename\core\value\text\modelfield|null
     */
    protected function getModelfieldInstanceRecursive(string $field): ?\codename\core\value\text\modelfield {
      $initialInstance = $this->getModelfieldInstance($field);

      // Already defined (schema+table+field)
      if($initialInstance->getSchema()) {
        return $initialInstance;
      }

      if(!$initialInstance->getSchema() || !$initialInstance->getTable()) {
        // Schema or even table not defined, search for it.
        if($initialInstance->getTable()) {
          // table is already defined, compare to current model and perform checks
          if($initialInstance->getTable() == $this->table) {
            if(in_array($initialInstance->get(), $this->getFields())) {
              return $this->getModelfieldInstance($this->schema.'.'.$this->table.'.'.$initialInstance->get());
            }
          }
        } else {
          // search by field only
          if(in_array($initialInstance->get(), $this->getFields())) {
            return $this->getModelfieldInstance($this->schema.'.'.$this->table.'.'.$initialInstance->get());
          }
        }
      }

      // Traverse tree
      foreach($this->getNestedJoins() as $join) {
        if($instance = $join->model->getModelfieldInstanceRecursive($field)) {
          return $instance;
        }
      }

      return null;
    }

    /**
     * [getModelfieldVirtualInstance description]
     * @param  string                            $field [description]
     * @return \codename\core\value\text\modelfield        [description]
     */
    protected function getModelfieldVirtualInstance(string $field): \codename\core\value\text\modelfield {
      return \codename\core\value\text\modelfield\virtual::getInstance($field);
    }

    /**
     * Normalizes a single row of a dataset
     * @param array $dataset
     */
    protected function normalizeRow(array $dataset) : array {
        if(\count($dataset) == 1 && isset($dataset[0])) {
            $dataset = $dataset[0];
        }

        foreach($dataset as $field=>$thisRow) {

          // Performance optimization (and fix):
          // Check for (key == null) first, as it is faster than is_string
          // NOTE: checking for !is_string commented-out
          // we need to check - at least for booleans (DB provides 0 and 1 instead of true/false)
          // if($dataset[$field] === null || !is_string($dataset[$field])) {continue;}
          if($dataset[$field] === null) { continue; }

          // special case: we need boolean normalization (0 / 1)
          // but otherwise, just skip
          if(
            ( isset($this->normalizeModelFieldTypeCache[$field]) && ($this->normalizeModelFieldTypeCache[$field] !== 'boolean'))
            && !\is_string($dataset[$field])
          ) { continue; }

            // determine virtuality status of the field
            if(!isset($this->normalizeModelFieldTypeVirtualCache[$field])) {
              $tVirtualModelField = $this->getModelfieldVirtualInstance($field);
              $this->normalizeModelFieldTypeCache[$field] = $this->getFieldtype($tVirtualModelField);
              $this->normalizeModelFieldTypeVirtualCache[$field] = $this->normalizeModelFieldTypeCache[$field] === 'virtual';
            }

            ///
            /// Fixing a bad performance issue
            /// using result-specific model field caching
            /// as they're re-constructed EVERY call!
            ///
            if(!isset($this->normalizeModelFieldCache[$field])) {
              if($this->normalizeModelFieldTypeVirtualCache[$field]) {
                $this->normalizeModelFieldCache[$field] = $this->getModelfieldVirtualInstance($field);
              } else {
                $this->normalizeModelFieldCache[$field] = $this->getModelfieldInstance($field);
              }
            }

            if(!isset($this->normalizeModelFieldTypeCache[$field])) {
              $this->normalizeModelFieldTypeCache[$field] = $this->getFieldtype($this->normalizeModelFieldCache[$field]);
            }

            //
            // HACK: only normalize boolean fields
            //
            if($this->normalizeModelFieldTypeCache[$field] === 'boolean') {
              $dataset[$field] = $this->importField($this->normalizeModelFieldCache[$field], $dataset[$field]);
              continue;
            }

            if(!isset($this->normalizeModelFieldTypeStructureCache[$field])) {
              $this->normalizeModelFieldTypeStructureCache[$field] = strpos($this->normalizeModelFieldTypeCache[$field], 'structu') !== false;
            }

            if($this->normalizeModelFieldTypeStructureCache[$field] && !is_array($dataset[$field])) {
              $dataset[$field] = $dataset[$field] == null ? null : app::object2array(json_decode($dataset[$field], false)/*, 512, JSON_UNESCAPED_UNICODE)*/);
            }

        }
        return $dataset;
    }

    /**
     * function is required to remove the default filter from the number generator
     * @return [type] [description]
     */
    public function removeDefaultFilter() {
      $this->defaultfilter = [];
      $this->defaultAggregateFilter = [];
      $this->defaultflagfilter = [];
      $this->defaultfilterCollections = [];
      return $this;
    }

    /**
     * resets all the parameters of the instance for another query
     * @return void
     */
    public function reset() {
        $this->cache = false;
        // $this->fieldlist = array();
        // $this->hiddenFields = array();
        $this->filter = $this->defaultfilter;
        $this->aggregateFilter = $this->defaultAggregateFilter;
        $this->flagfilter = $this->defaultflagfilter;
        $this->filterCollections = $this->defaultfilterCollections;
        $this->limit = null;
        $this->offset = null;
        $this->filterDuplicates = false;
        $this->order = array();
        $this->errorstack->reset();
        foreach($this->nestedModels as $nest) {
          $nest->model->reset();
        }
        // TODO: reset collection models?
        return;
    }

    /**
     * internal variable containing field types for a given field
     * to improve performance of ::importField
     * @var [type]
     */
    protected $importFieldTypeCache = [];

    protected $fieldTypeCache = [];

    protected function importFieldImproved(string $field, $value = null) {
      $fieldType = $this->fieldTypeCache[$field] ?? $this->fieldTypeCache[$field] = $this->getFieldtypeImproved($field);
      switch($fieldType) {
        case 'number_natural':
          if(\is_string($value) && \strlen($value) === 0) {
              return null;
          }
          break;
        case 'boolean' :
          // allow null booleans
          // may be needed for conditional unique keys
          if(\is_null($value)) {
              return $value;
          }
          // pure boolean
          if(\is_bool($value)) {
              return $value;
          }
          // int: 0 or 1
          if(\is_int($value)) {
              if($value !== 1 && $value !== 0) {
                throw new exception('EXCEPTION_MODEL_IMPORTFIELD_BOOLEAN_INVALID', exception::$ERRORLEVEL_ERROR, [
                  'field' => $field,
                  'value' => $value
                ]);
              }
              return $value === 1 ? true : false;
          }
          // string boolean
          if(\is_string($value)) {
            // fallback, empty string
            if(\strlen($value) === 0) {
              return null;
            }
            if($value === '1') {
              return true;
            } else if($value === '0') {
              return false;
            } else if($value === 'true') {
              return true;
            } elseif ($value === 'false') {
              return false;
            }
          }
          // fallback
          return false;
          break;
        case 'text_date':
          if(\is_null($value)) {
              return $value;
          }
          // automatically convert input value
          // ctor returns FALSE on creation error, see http://php.net/manual/de/datetime.construct.php
          $date = new \DateTime($value);
          if($date !== false) {
              return $date->format('Y-m-d');
          }
          return null;
          break;
      }
      return $value;
    }

    /**
     * Converts the given field and it's value from a human readible format into a storage format
     * @param \codename\core\value\text\modelfield $field
     * @param unknown $value
     * @return multitype
     */
    protected function importField(\codename\core\value\text\modelfield $field, $value = null) {
        $fieldType = $this->importFieldTypeCache[$field->get()] ?? $this->importFieldTypeCache[$field->get()] = $this->getFieldtype($field);
        switch($fieldType) {
            case 'number_natural':
              if(\is_string($value) && \strlen($value) === 0) {
                  return null;
              }
              break;
            case 'boolean' :
                // allow null booleans
                // may be needed for conditional unique keys
                if(\is_null($value)) {
                    return $value;
                }
                // pure boolean
                if(\is_bool($value)) {
                    return $value;
                }
                // int: 0 or 1
                if(\is_int($value)) {
                    if($value !== 1 && $value !== 0) {
                      throw new exception('EXCEPTION_MODEL_IMPORTFIELD_BOOLEAN_INVALID', exception::$ERRORLEVEL_ERROR, [
                        'field' => $field->get(),
                        'value' => $value
                      ]);
                    }
                    return $value === 1 ? true : false;
                }
                // string boolean
                if(\is_string($value)) {
                  // fallback, empty string
                  if(\strlen($value) === 0) {
                    return null;
                  }
                  if($value === '1') {
                    return true;
                  } else if($value === '0') {
                    return false;
                  } else if($value === 'true') {
                    return true;
                  } elseif ($value === 'false') {
                    return false;
                  }
                }
                // fallback
                return false;
                break;
            case 'text_date':
                if(\is_null($value)) {
                    return $value;
                }
                // automatically convert input value
                // ctor returns FALSE on creation error, see http://php.net/manual/de/datetime.construct.php
                $date = new \DateTime($value);
                if($date !== false) {
                    return $date->format('Y-m-d');
                }
                return null;
                break;
            /* case 'text' :
                if(is_null($value)) {
                    return $value;
                }
                return str_replace($this->delimiter, '#__DELIMITER__#', $value);
                break; */
        }
        return $value;
    }

    /**
     * Returns the driver that shall be used for the model
     * @return string
     */
    protected function getType() : string {
        return static::DB_TYPE;
    }

    /**
     * [delimitImproved description]
     * @param  string $field [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function delimitImproved(string $field, $value = null) {
      $fieldtype = $this->fieldTypeCache[$field] ?? $this->fieldTypeCache[$field] = $this->getFieldtypeImproved($field);

      // CHANGED 2020-12-30 removed \is_string($value) && \strlen($value) == 0
      // Which converted '' to NULL - which is simply wrong.
      if($value === null) {
        return null;
      }

      // if(strpos($fieldtype, 'text') !== false || strpos($fieldtype, 'ject_') !== false || strpos($fieldtype, 'structure') !== false) {
      //     return "" . $value . "";
      // }
      if($fieldtype == 'number') {
        if(\is_numeric($value)) {
          return $value;
        }
        if(\strlen($value) == 0) {
          return null;
        }
        return $value;
      }
      if($fieldtype == 'number_natural') {
        if(\is_int($value)) {
          return $value;
        }
        if(\is_string($value) && \strlen($value) == 0) {
          return null;
        }
        return (int) $value;
      }
      if($fieldtype == 'boolean') {
        if(\is_string($value) && \strlen($value) == 0) {
          return null;
        }
        if($value) {
          return true;
        }
        return false;
      }
      if(strpos($fieldtype, 'text') === 0) {
        if(\is_string($value) && \strlen($value) == 0) {
          return null;
        }
      }
      return $value;
    }

    /**
     * Returns the field's value as a string.
     * <br />It delimits the field using a colon if it is required by the field's datatype
     * @param \codename\core\value\text\modelfield $field
     * @param string $value
     * @return string
     */
    protected function delimit(\codename\core\value\text\modelfield $field, $value = null) {
        $fieldtype = $this->getFieldtype($field);

        // CHANGED 2020-12-30 removed \is_string($value) && \strlen($value) == 0
        // Which converted '' to NULL - which is simply wrong.
        if($value === null) {
          return null;
        }

        // if(strpos($fieldtype, 'text') !== false || strpos($fieldtype, 'ject_') !== false || strpos($fieldtype, 'structure') !== false) {
        //     return "" . $value . "";
        // }
        if($fieldtype == 'number') {
            if(is_numeric($value)) {
              return $value;
            }
            if(strlen($value) == 0) {
              return null;
            }
            return $value;
        }
        if($fieldtype == 'number_natural') {
            if(is_int($value)) {
              return $value;
            }
            if(is_string($value) && strlen($value) == 0) {
              return null;
            }
            return (int) $value;
        }
        if($fieldtype == 'boolean') {
            if(\is_string($value) && \strlen($value) == 0) {
              return null;
            }
            if($value) {
                return true;
            }
            return false;
        }
        if(strpos($fieldtype, 'text') === 0) {
          if(\is_string($value) && \strlen($value) == 0) {
            return null;
          }
        }
        return $value;
    }

    /**
     * Returns the cachegroup identifier for this model
     * @return string
     * @todo prevent collision by using the PSR-4 namespace from ReflectionClass::
     */
    protected function getCacheGroup() : string {
        return get_class($this);
    }

    /**
     * Deletes dependencies of elements in this model
     * @return void
     */
    protected function deleteChildren(string $primaryKey) {
        return;
    }

    /**
     * Gets the current model identifier (name)
     * @return string
     */
    public abstract function getIdentifier() : string;

}
