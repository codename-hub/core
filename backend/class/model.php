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
    CONST EXCEPTION_ENTRYSETFLAG_UPDATEELEMENTEMPTY = 'EXCEPTION_ENTRYSETFLAG_UPDATEELEMENTEMPTY';

    /**
     * The loaded entry does not contain flags
     * @var string
     */
    CONST EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL';

    /**
     * You want to unset a flag but the element is empty
     * @var string
     */
    CONST EXCEPTION_ENTRYUNSETFLAG_UPDATEELEMENTEMPTY = 'EXCEPTION_ENTRYUNSETFLAG_UPDATEELEMENTEMPTY';

    /**
     * You want to unset a flag but there are no flags in this model
     * @var string
     */
    CONST EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL = 'EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL';

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
     * Incompatible models during addSibling
     * @var string
     */
    CONST EXCEPTION_ADDSIBLING_CANNOTADDSIBLING = "EXCEPTION_ADDSIBLING_CANNOTADDSIBLING";

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
     * @var db
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
     * loads a new config file (uncached)
     * implement me!
     * @return \codename\core\config
     */
    protected abstract function loadConfig() : \codename\core\config;

    /**
     * [getNestedJoins description]
     * @return \codename\core\model\plugin\join[]
     */
    public function getNestedJoins() : array {
        return $this->nestedModels;
    }

    /**
     * [getSiblingJoins description]
     * @return \codename\core\model\plugin\join[]
     */
    public function getSiblingJoins() : array {
        return $this->siblingModels;
    }

    /**
     * determines if the model is joinable
     * in the same run (e.g. DB compatibility and stuff)
     * @return bool
     */
    protected function compatibleJoin(\codename\core\model $model) : bool {
      return $this->getType() == $model->getType();
    }

    /**
     * @var array
     */
    // protected $siblingJoins = array();
    /*
    protected function getSiblingJoin(string $key) : array {
        return $this->siblingJoins[$key];
    }*/

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
        if(is_null($this->data)) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDSET_NOOBJETLOADED, \codename\core\exception::$ERRORLEVEL_FATAL);
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
        if(is_null($this->data)) {
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
        if(is_null($this->data)) {
            return $this;
        }
        if(is_null($this->data)) {
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
     * @todo DOCUMENTATION
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

        if(($thisKey == null) || ($joinKey == null)) {
          throw new exception('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION', exception::$ERRORLEVEL_ERROR, array($this->getIdentifier(), $model->getIdentifier(), $modelField, $referenceField));
        }

        // fallback to bare model joining
        $pluginDriver = $this->compatibleJoin($model) ? $this->getType() : 'bare';

        $class = '\\codename\\core\\model\\plugin\\join\\' . $pluginDriver;
        array_push($this->nestedModels, new $class($model, $type, $thisKey, $joinKey, $conditions));
        // check for already-added ?

        return $this;
    }

    /**
     * contains configured join plugin instances for nested models
     * @var \codename\core\model\plugin\join[]
     */
    protected $nestedModels = array();

    /**
     * contains configured join plugin instances for sibling models
     * @var \codename\core\model\plugin\join[]
     */
    protected $siblingModels = array();

    /**
     * Adds a model as a Sibling (and not as a nested model)
     * to be used for joining two or more tables by foreign keys, without another model/table in-between
     */
    public function addSiblingModel(\codename\core\model $model, string $type = plugin\join::TYPE_LEFT, string $modelField = null, string $referenceField = null) : \codename\core\model {
        /* $this->tables[] = array(
                'schema' => $model->schema,
                'table' => $model->table,
                'primarykey' => $model->getPrimarykey()
        );*/
        if($this->config->exists("foreign")) {
          foreach($this->config->get("foreign") as $thisForeignKey => $thisForeign) {
            if($model->config->exists("foreign")) {
              foreach($model->config->get("foreign") as $otherForeignKey => $otherForeign) {
                if($thisForeign['model'] == $otherForeign['model']) {
                  // we have a cross-like join
                  //  $this->siblings[] = $model;


                  // TODO: should we switch ?

                  // if a specific model field is given, use it as requirement
                  if($modelField != null && $modelField != $thisForeign['key']) {
                    continue;
                  }

                  // if a specific reference field is given, use it as requirement
                  if($referenceField != null && $referenceField != $otherForeign['key']) {
                    continue;
                  }

                  $class = '\\codename\\core\\model\\plugin\\join\\' . $this->getType();
                  array_push($this->siblingModels, new $class($model, $type, $thisForeignKey, $otherForeignKey));
                  /*
                  $this->siblingJoins[$model->schema.'.'.$model->table] = array(
                    'this_field' => $thisForeignKey,
                    'sibling_field' => $otherForeignKey
                  );*/
                  return $this;
                }
              }
            }
          }
        }
        throw new \codename\core\exception(self::EXCEPTION_ADDSIBLING_CANNOTADDSIBLING, \codename\core\exception::$ERRORLEVEL_ERROR, array($this->table, $model->table));
    }

    /**
     * Autocombine models given in an array
     * returns an automatically joined base model, if possible.
     * @param string[] $modelNames [array of model names]
     * @return \codename\core\model
     */
    public static function getAutocombinedModels($modelNames) : \codename\core\model {

      $models = array();
      foreach($modelNames as $modelName) {
        $models[$modelName] = app::getModel($modelName);
      }
      $availableModels = $models;
      if(sizeof($models) > 1) {
        foreach($availableModels as $mName => &$m) {
          if($m->config->exists('foreign')) {
            foreach($m->config->get('foreign') as $foreign) {
              if(array_key_exists($foreign['model'], $availableModels)) {
                if($m !== $availableModels[$foreign['model']]) { // prevent self-add
                  $m->addModel($availableModels[$foreign['model']]);
                  unset($models[$foreign['model']]);
                }
              }
            }
          }
        }
      }

      if(sizeof($models) == 1) {
        // Model tree reduced to a single root model - finished.
        $baseModel = $models[key($models)];
      } else {
        // Try to join the models as siblings
        reset($models);
        $firstKey = key($models);
        $baseModel = $models[$firstKey];
        unset($models[$firstKey]);

        // try to join them as siblings:
        foreach($models as $mName => &$m) {
          $baseModel->addSiblingModel($m);
          unset($models[$mName]);
        }

        if(sizeof($models) > 0) {
          $unjoinable = array_keys($models);
          throw new \codename\core\exception(self::EXCEPTION_AUTOCOMBINEMODELS_UNJOINABLE_MODELS, \codename\core\exception::$ERRORLEVEL_ERROR, $unjoinable);
        }
      }
      return $baseModel;
    }

    /**
     * I load an entry of the given model identified by the $primarykey to the current instance.
     * @param string $primaryKey
     * @return \codename\core\model
     */
    public function entryLoad(string $primaryKey) : \codename\core\model {
        $entry = $this->loadByUnique($this->getPrimarykey(), $primaryKey);
        if(count($entry) == 0) {
            return $this;
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
        if(is_null($this->data)) {
            return $this;
        }
        if(is_null($this->data)) {
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
        if(is_null($this->data)) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        foreach($this->getFields() as $field) {
            if(array_key_exists($field, $data)) {
                $this->fieldSet(\codename\core\value\text\modelfield::getInstance($field), $data[$field]);
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
        if(is_null($this->data)) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYSETFLAG_UPDATEELEMENTEMPTY, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if(!$this->config->exists('flag')) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }

        $flag = $this->fieldGet(\codename\core\value\text\modelfield::getInstance($this->table . '_flag'));
        $flag |= $flagval;
        $this->fieldSet(\codename\core\value\text\modelfield::getInstance($this->table . '_flag'), $flag);
        return $this;
    }

    /**
     * I set a flag (identified by the integer $flagval) to FALSE.
     * @param int $flagval
     * @throws \codename\core\exception
     * @return \codename\core\model
     */
    public function entryUnsetflag(int $flagval) : \codename\core\model {
        if(is_null($this->data)) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUNSETFLAG_UPDATEELEMENTEMPTY, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        if(!$this->config->exists('flag')) {
            throw new \codename\core\exception(self::EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, null);
        }
        $flag = $this->fieldGet(\codename\core\value\text\modelfield::getInstance($this->table . '_flag'));
        $flag &= ~$flagval;
        $this->fieldSet(\codename\core\value\text\modelfield::getInstance($this->table . '_flag'), $flag);
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
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addFilter($field, $value, $operator)
     */
    public function addFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if(is_array($value)) {
            if(count($value) == 0) {
                return $this;
            }
            array_push($this->filter, new $class(\codename\core\value\text\modelfield::getInstance($field), $value, $operator, $conjunction));
        } else {
            array_push($this->filter, new $class(\codename\core\value\text\modelfield::getInstance($field), $this->delimit(new \codename\core\value\text\modelfield($field), $value), $operator, $conjunction));
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addFilter($field, $value, $operator)
     */
    public function addFieldFilter(string $field, string $otherField, string $operator = '=', string $conjuction = null) : model {
        $class = '\\codename\\core\\model\\plugin\\fieldfilter\\' . $this->getType();
        array_push($this->filter, new $class(\codename\core\value\text\modelfield::getInstance($field), new \codename\core\value\text\modelfield($otherField), $operator, $conjuction));
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
    public function addFilterCollection(array $filters, string $groupOperator = null, string $groupName = 'default', string $conjunction = null) : model {
      $filterCollection = array();
      foreach($filters as $filter) {
        $field = $filter['field'];
        $value = $filter['value'];
        $operator = $filter['operator'];
        $filter_conjunction = $filter['conjunction'] ?? null;
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();
        if(is_array($value)) {
            if(count($value) == 0) {
                continue;
            }
            array_push($filterCollection, new $class(\codename\core\value\text\modelfield::getInstance($field), $value, $operator, $filter_conjunction));
        } else {
            array_push($filterCollection, new $class(\codename\core\value\text\modelfield::getInstance($field), $this->delimit(new \codename\core\value\text\modelfield($field), $value), $operator, $filter_conjunction));
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
                continue;
            }
            array_push($filterCollection, new $class(\codename\core\value\text\modelfield::getInstance($field), $value, $operator, $filter_conjunction));
        } else {
            array_push($filterCollection, new $class(\codename\core\value\text\modelfield::getInstance($field), $this->delimit(new \codename\core\value\text\modelfield($field), $value), $operator, $filter_conjunction));
        }
      }
      if(sizeof($filterCollection) > 0) {
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
        $field = \codename\core\value\text\modelfield::getInstance($field);
        // if(!$this->fieldExists($field)) {
        //     throw new \codename\core\exception(self::EXCEPTION_ADDDEFAULTFILTER_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        // }
        $class = '\\codename\\core\\model\\plugin\\filter\\' . $this->getType();

        if(is_array($value)) {
            if(count($value) == 0) {
                return $this;
            }
            array_push($this->defaultfilter, new $class($field, $value, $operator, $conjunction));
            array_push($this->filter, new $class($field, $value, $operator, $conjunction));
        } else {
            array_push($this->defaultfilter, new $class($field, $this->delimit($field, $value), $operator, $conjunction));
            array_push($this->filter, new $class($field, $this->delimit($field, $value), $operator, $conjunction));
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addOrder($field, $order)
     */
    public function addOrder(string $field, string $order = 'ASC') : model {
        $field = \codename\core\value\text\modelfield::getInstance($field);
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
     *
     * {@inheritDoc}
     * @see \codename\core\model_interface::addField($field)
     */
    public function addField(string $field) : model {
        if(strpos($field, ',') !== false) {
            foreach(explode(',', $field) as $myField) {
                $this->addField(trim($myField));
            }
            return $this;
        }

        $field = \codename\core\value\text\modelfield::getInstance($field);
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_ADDFIELD_FIELDNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        }

        $class = '\\codename\\core\\model\\plugin\\field\\' . $this->getType();
        $this->fieldlist[] = new $class($field);
        return $this;
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
     * groupBy fields
     * @var \codename\core\model\plugin\group[]
     */
    protected $group = array();

    /**
     * @inheritDoc
     */
    public function addGroup(string $field) : model {
      $field = \codename\core\value\text\modelfield::getInstance($field);
      if(!$this->fieldExists($field)) {
        $foundInFieldlist = false;
        foreach($this->fieldlist as $checkField) {
          if($checkField->field->get() == $field->get()) {
            $foundInFieldlist = true;
            break;
          }
        }
        if($foundInFieldlist === false) {
          throw new \codename\core\exception(self::EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST, \codename\core\exception::$ERRORLEVEL_FATAL, array($field, $this->fieldlist));
        }
      }
      $class = '\\codename\\core\\model\\plugin\\group\\' . $this->getType();
      $this->group[] = new $class($field);
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
      $field = \codename\core\value\text\modelfield::getInstance($field);
      // only check for EXISTANCE of the fieldname, cancel if so - we don't want duplicates!
      if($this->fieldExists($field)) {
        throw new \codename\core\exception(self::EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
      }
      $class = '\\codename\\core\\model\\plugin\\calculatedfield\\' . $this->getType();
      $this->fieldlist[] = new $class($field, $calculation);
      return $this;
    }

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

        if($field == $this->getPrimarykey()) {
            $cacheObj = app::getCache();
            $cacheGroup = $this->getCachegroup();
            $cacheKey = "PRIMARY_" . $value;

            $myData = $cacheObj->get($cacheGroup, $cacheKey);
            if(!is_array($myData) || count($myData) == 0) {
                $myData = $data->search()->getResult();
                if(count($myData) == 1) {
                    $cacheObj->set($cacheGroup, $cacheKey, $myData);
                }
            }
            if(count($myData) > 0) {
                return $myData[0];
            }
            return array();
        }

        $data->useCache();
        $data = $data->search()->getResult();
        if(count($data) == 0) {
            return array();
        }
        return $data[0];
    }

    /**
     * Returns the datatype of the given field
     * @param \codename\core\value\text\modelfield $field
     * @return string
     */
    public function getFieldtype(\codename\core\value\text\modelfield $field) : string {
      $specifier = $field->get();
      if(array_key_exists($specifier, $this->cachedFieldtype)) {
        return $this->cachedFieldtype[$specifier];
      } else {

        // fieldtype not in current model config
        if(!$this->config->exists("datatype>" . $specifier)) {
          // check nested model configs
          foreach($this->nestedModels as $joinPlugin) {
            $fieldtype = $joinPlugin->model->getFieldtype($field);
            if($fieldtype != 'text') {
              return $fieldtype;
            }
          }

          // check sibling model configs
          foreach($this->siblingModels as $joinPlugin) {
            $fieldtype = $joinPlugin->model->getFieldtype($field);
            if($fieldtype != 'text') {
              return $fieldtype;
            }
          }
          return 'text';
        }

        // use cached value
        $this->cachedFieldtype[$specifier] = $this->config->get("datatype>".$specifier);
        return $this->cachedFieldtype[$specifier];
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
     * primarykey cache field
     * @var string
     */
    protected $primarykey = null;

    /**
     * Returns the primary key that was configured in the model's JSON config
     * @return string
     */
    public function getPrimarykey() : string {
        if($this->primarykey == null) {
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
              $foreignConfig = $this->config->get('foreign>'.$childConfig['field']);
              $foreignKeyField = $childConfig['field'];

              // get the join plugin valid for the child reference field
              $res = array_filter($this->getNestedJoins(), function(\codename\core\model\plugin\join $join) use ($foreignKeyField) {
                return $join->modelField == $foreignKeyField;
              });

              if(count($res) === 1) {
                $join = reset($res);
                $join->model->validate($data[$field]);
                $this->errorstack->addErrors($join->model->getErrors());
              } else {
                continue;
              }
            }

            if (count($errors = app::getValidator($this->getFieldtype(\codename\core\value\text\modelfield::getInstance($field)))->reset()->validate($data[$field])) > 0) {
                $this->errorstack->addError($field, 'FIELD_INVALID', $errors);
            }
        }

        // model validator
        if($this->config->exists('validators')) {
          $validators = $this->config->get('validators');
          foreach($validators as $validator) {
            if(count($errors = app::getValidator($validator)->validate($data)) > 0) {
              $this->errorstack->addError('DATA', 'INVALID', $errors);
            }
          }
        }

        $dataob = $this->data;
        if(is_array($this->config->get("unique"))) {
            foreach($this->config->get("unique") as $key => $fields) {
                if(!is_array($fields)) {
                    continue;
                }
                $filtersApplied = 0;

                // exclude my own dataset if UPDATE is in progress
                if(array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0) {
                    $this->addFilter($this->getPrimarykey(), $data[$this->getPrimarykey()], '!=');
                }

                foreach($fields as $field) {
                    // if(!array_key_exists($field, $data) || strlen($data[$field]) == 0) {
                    //     continue;
                    // }
                    $this->addFilter($field, $data[$field] ?? null, '=');
                    $filtersApplied++;
                }
                if($filtersApplied == 0) {
                    continue;
                }

                if(count($this->search()->getResult()) > 0) {
                    $this->errorstack->addError($field, 'FIELD_DUPLICATE', $data[$field]);
                }
            }
        }
        $this->data = $dataob;

        return $this;
    }

    /**
     * normalizes data in the given array.
     * <br />Tries to identify complex datastructures by the Hiden $FIELDNAME."_" fields and makes objects of them
     * @param array $data
     * @return array
     */
    public function normalizeData(array $data) : array {
        $myData = array();
        foreach($this->config->get('field') as $field) {
            // if field has object identified
            if(array_key_exists($field.'_', $data)) {
                $object = array();
                foreach($data as $key => $value) {
                    if(strpos($key, $field.'__') !== false) {
                        $object[str_replace($field . '__', '', strtolower($key))] = $data[$key];
                    }
                }
                $myData[$field] = $object;
            }

            if($field == $this->table . '_flag') {
                if(array_key_exists($this->table . '_flag', $data)) {
                    if(!is_array($data[$this->table . '_flag'])) {
                        continue;
                    }

                    $flagval = 0;
                    foreach($data[$this->table . '_flag'] as $flagname => $status) {
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
            if(array_key_exists($field, $data)) {
                $myData[$field] = $this->importField(\codename\core\value\text\modelfield::getInstance($field), $data[$field]);
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
                return $value ? 'true' : 'false';
                break;
            case 'text_date':
                return date('Y-m-d', strtotime($value));
                break;
            case 'text' :
                return str_replace('#__DELIMITER__#', $this->delimiter, $value);
                break;
        }

        return $value;
    }

    /**
     * Returns true if there is a "complete" key in the given model configuration and the fields in this array are filled up
     * @param array $data
     * @return bool
     */
    public function isComplete(array $data) : bool {

        // Continue when no complete key is available
        if(!$this->config->exists('complete')) {
            return true;
        }

        // Validate the fields
        foreach($this->config->get('complete') as $field) {
            // Field does not exist
            if(!array_key_exists($field, $data)){
                $this->errorstack->addError($field, 'FIELD_IS_EMPTY', null);
                continue;
            }

            // Field is null
            if(is_null($data[$field])) {
                $this->errorstack->addError($field, 'FIELD_IS_EMPTY', null);
                continue;
            }

            // Field is empty string
            if(is_string($data[$field]) && strlen($data[$field]) == 0) {
                $this->errorstack->addError($field, 'FIELD_IS_EMPTY', null);
                continue;
            }

            // Validate arrays
            if(is_array($data[$field])) {
                foreach($data[$field] as $check) {
                    if(strlen($check) == 0) {
                        $this->errorstack->addError($field, 'FIELD_IS_EMPTY', null);
                        continue;
                    }
                }
            }
        }

        if(count($this->getErrors()) > 0) {
            return false;
        }

        return true;
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
          foreach($this->getSiblingJoins() as $join) {
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
                $query,
                array_merge($this->filter, $this->filterCollections),
                $params
              )
            ));
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

         if (is_null($result)) {
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
     * @return array
     */
    protected function performBareJoin(array $result) : array {
      if(count($this->getNestedJoins()) == 0 && count($this->getSiblingJoins()) == 0) {
        return $result;
      }
      foreach($this->getNestedJoins() as $join) {
        $nest = $join->model;

        // check model joining compatible
        // we explicitly join incompatible models using a bare-data here!
        if(!$this->compatibleJoin($nest) && ($join instanceof \codename\core\model\plugin\join\executableJoinInterface)) {
          $subresult = $nest->search()->getResult();
          $result = $join->join($result, $subresult);
        }
      }
      foreach($this->getSiblingJoins() as $join) {
        $nest = $join->model;

        // check model joining compatible
        // we explicitly join incompatible models using a bare-data join here!
        if(!$this->compatibleJoin($nest) && ($join instanceof \codename\core\model\plugin\join\executableJoinInterface)) {
          $subresult = $nest->search()->getResult();
          $result = $join->join($result, $subresult);
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

        $this->normalizeModelFieldCache = array();
        $this->normalizeModelFieldTypeCache = array();

        // Normalize single row
        if(count($result) == 1) {
            $result = $result[0];
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
     * Normalizes a single row of a dataset
     * @param array $dataset
     */
    protected function normalizeRow(array $dataset) : array {
        if(count($dataset) == 1 && isset($dataset[0])) {
            $dataset = $dataset[0];
        }

        foreach($dataset as $field=>$thisRow) {

            // Performance optimization (and fix):
            // Check for (key == null) first, as it is faster than is_string
            if($dataset[$field] == null || !is_string($dataset[$field])) {continue;}

            ///
            /// Fixing a bad performance issue
            /// using result-specific model field caching
            /// as they're re-constructed EVERY call!
            ///
            if(!isset($this->normalizeModelFieldCache[$field])) {
              $this->normalizeModelFieldCache[$field] = \codename\core\value\text\modelfield::getInstance($field);
            }

            if(!isset($this->normalizeModelFieldTypeCache[$field])) {
              $this->normalizeModelFieldTypeCache[$field] = $this->getFieldtype($this->normalizeModelFieldCache[$field]);
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
     * resets all the parameters of the instance for another query
     * @return void
     */
    public function reset() {
        $this->cache = false;
        $this->fieldlist = array();
        $this->hiddenFields = array();
        $this->filter = $this->defaultfilter;
        $this->flagfilter = $this->defaultflagfilter;
        $this->filterCollections = $this->defaultfilterCollections;
        $this->limit = null;
        $this->offset = null;
        $this->filterDuplicates = false;
        $this->order = array();
        $this->errorstack->reset();
        return;
    }

    /**
     * Converts the given field and it's value from a human readible format into a storage format
     * @param \codename\core\value\text\modelfield $field
     * @param unknown $value
     * @return multitype
     */
    protected function importField(\codename\core\value\text\modelfield $field, $value = null) {
        switch($this->getFieldtype($field)) {
            case 'boolean' :
                if(strlen($value) == 0) {
                    return null;
                }
                if($value == 'true') {
                    return true;
                }
                return false;
                break;
            case 'text_date':
                if(is_null($value)) {
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
     * Returns the field's value as a string.
     * <br />It delimits the field using a colon if it is required by the field's datatype
     * @param \codename\core\value\text\modelfield $field
     * @param string $value
     * @return string
     */
    protected function delimit(\codename\core\value\text\modelfield $field, $value = null) {
        $fieldtype = $this->getFieldtype($field);
        if(is_null($value) || (is_string($value) && strlen($value) == 0)) {
          return null;
        }
        if(strpos($fieldtype, 'text') !== false || strpos($fieldtype, 'ject_') !== false || strpos($fieldtype, 'structure') !== false) {
            return "" . $value . "";
        }
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
            if($value) {
                return true;
            }
            return false;
        }
        return $value;
    }

    /**
     * I remove all special SQL characters from a string.
     * @param string $string
     * @return string
     */
    protected function cleanString(string $string) : string {
        foreach(explode(',', '*,%,--') as $char) {
            $string = str_replace($char, '', $string);
        }
        $string = str_replace($this->delimiter, $this->delimiter.$this->delimiter, $string);
        return $string;
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
     * Tries loading the given $cacheKey from the cache
     * @param string $cacheKey
     * @return array | multitype
     */
    protected function getFromCache(string $cacheKey) {
        return $this->getCache()->get($this->getCacheGroup(), $cacheKey);
    }

    /**
     * Writes the given $data to the given $cacheKey
     * @param string $cacheKey
     * @param unknown $data
     */
    protected function writeToCache(string $cacheKey, array $data) {
        return $this->getCache()->set($this->getCacheGroup(), $cacheKey, $data);
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
