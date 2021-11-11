<?php
namespace codename\core\model;

use codename\core\app;
use codename\core\model;
use codename\core\config;
use codename\core\exception;

/**
 * abstractDynamicValueModel
 */
abstract class abstractDynamicValueModel extends \codename\core\model {

  /**
   * [DB_TYPE description]
   * @var string
   */
  const DB_TYPE = 'bare';

  /**
   * dummy "table" name
   * @var string
   */
  public $table = null;

  /**
   * dummy "schema" name
   * @var string
   */
  public $schema = null;

  /**
   * data model internally used for storing stuff
   * @var \codename\core\model
   */
  protected $dataModel;

  /**
   * [__construct description]
   * @param array $modeldata [description]
   */
  public function __construct(array $modeldata)
  {
    $this->errorstack = new \codename\core\errorstack('MODEL_ABSTRACT_DYNAMIC_VALUE');
    $this->initializeDataModel();
  }

  /**
   * [normalizeRecursivelyByFieldlist description]
   * @param  array $result [description]
   * @return array         [description]
   */
  public function normalizeRecursivelyByFieldlist(array $result) : array {
    $fResult = [];
    //
    // normalize
    // TODO: Sibling Joins?
    //
    foreach($this->getNestedJoins() as $join) {
      // normalize using nested model - BUT: only if it's NOT already actively used as a child virtual field
      $found = false;
      if(($children = $this->config->get('children')) != null) {
        foreach($children as $field => $config) {
          if($config['type'] === 'foreign') {
            $foreign = $this->config->get('foreign>'.$config['field']);
            if($foreign['model'] === $join->model->getIdentifier()) {
              if($this->config->get('datatype>'.$field) == 'virtual') {
                $found = true;
                break;
              }
            }
          }
        }
      }
      if($found) {
        continue;
      }

      /**
       * FIXME @Kevin: Weil wegen Baum und sehr sehr russisch
       * @var [type]
       */
      if($join->model instanceof \codename\core\model\schemeless\json) {
        continue;
      }

      $normalized = $join->model->normalizeRecursivelyByFieldlist($result);

      // // METHOD 1: merge manually, row by row
      foreach($normalized as $index => $r) {
        // normalize using this model
        $fResult[$index] = array_merge(($fResult[$index] ?? []), $r);
      }
    }

    //
    // Normalize using this model's fields
    //
    foreach($result as $index => $r) {
      // normalize using this model
      $fResult[$index] = array_merge(($fResult[$index] ?? []), $this->normalizeByFieldlist($r));
    }

    return $fResult;
  }

  /**
   * [normalizeByFieldlist description]
   * @param  array $dataset [description]
   * @return array          [description]
   */
  public function normalizeByFieldlist(array $dataset) : array {
    if(count($this->hiddenFields) > 0) {
      // explicitly keep out hidden fields
      $dataset = array_diff_key($dataset, array_flip( $this->hiddenFields ));
    }
    if(count($this->fieldlist) > 0) {
      // return $dataset;
      return array_intersect_key( $dataset, array_flip( array_merge( $this->getFieldlistArray($this->fieldlist), $this->getFields(), array_keys($this->virtualFields) ) ) );
    } else {
      // return $dataset;
      return array_intersect_key( $dataset, array_flip( array_merge( $this->getFields(), array_keys($this->virtualFields)) ) );
    }
  }


  /**
   * initialized the datamodel. to be overridden
   * @return void
   */
  protected abstract function initializeDataModel();

  /**
   * pseudo-function for setting schema & table
   *
   * @param  string|null          $connection [no-op]
   * @param  string               $schema     [description]
   * @param  string               $table      [description]
   * @return model             [description]
   */
  public function setConfig(string $connection = null, string $schema, string $table) : model {
    $this->schema = $schema;
    $this->table = $table;
    return $this;
  }

  /**
   * [setConfig description]
   * @param  string|null $file           [description]
   * @param  config|null $configInstance [description]
   * @return model                   [description]
   */
  protected function setDynamicConfig(?string $file = null, ?config $configInstance = null) : model {
    if(!$file && !$configInstance) {
      throw new exception('ABSTRACT_DYNAMIC_VALUE_MODEL_INVALID_CONFIGURATION', exception::$ERRORLEVEL_FATAL);
    }
    if($configInstance) {
      $this->dynamicConfig = $configInstance;
    } else {
      $this->dynamicConfig = new \codename\core\config\json($file, true, true);
    }
    return $this;
  }

  /**
   * contains a pkey filter value, if active
   * @var [type]
   */
  protected $primaryKeyFilterValue = null;

  /**
   * contains a pkey default filter value, if active
   * @var [type]
   */
  protected $primaryKeyDefaultFilterValue = null;

  /**
   * array of to-be-used filters
   * @var [type]
   */
  protected $filterValues = [];

  /**
   * default filter values
   * @var [type]
   */
  protected $defaultFilterValues = [];

  /**
   * @inheritDoc
   */
  public function addFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
    if($field === $this->primarykey && $value && $operator === '=' && $conjunction === null) {
      $this->primaryKeyFilterValue = $value;
    } else {
      if(in_array($field, $this->getFields()) && $value && $operator === '=' && $conjunction === null) {
        $this->filterValues[$field] = $value;
      } else {
        throw new exception('MODEL_ABSTRACT_DYNAMIC_VALUE_MODEL_UNSUPPORTED_OPERATION_ADDFILTER', exception::$ERRORLEVEL_ERROR, [
          'field' => $field,
          'value' => $value,
          'operator' => $operator,
          'conjunction' => $conjunction
        ]);
      }
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function addDefaultFilter(string $field, $value = null, string $operator = '=', string $conjunction = null) : model {
    // echo("add Default filter ". $field .", ". $value.", ".  $operator.", ".  $conjunction. " pkey: ".$this->primarykey);
    if($field === $this->primarykey && $value && $operator === '=' && $conjunction === null) {
      $this->primaryKeyDefaultFilterValue = $value;
      $this->primaryKeyFilterValue = $this->primaryKeyDefaultFilterValue;
    } else {
      if(in_array($field, $this->getFields()) && $value && $operator === '=' && $conjunction === null) {
        $this->defaultFilterValues[$field] = $value;
        $this->filterValues[$field] = $this->defaultFilterValues[$field];
      } else {
        throw new exception('MODEL_ABSTRACT_DYNAMIC_VALUE_MODEL_UNSUPPORTED_OPERATION_ADDDEFAULTFILTER', exception::$ERRORLEVEL_ERROR, [
          'field' => $field,
          'value' => $value,
          'operator' => $operator,
          'conjunction' => $conjunction
        ]);
      }
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setLimit(int $limit) : model
  {
    return $this;
  }

  /**
   * the configuration for the dynamic model parts
   * @var config
   */
  protected $dynamicConfig = null;

  /**
   * enables setting the primary key
   * which may be a special reference field in the data model
   * @param string      $name           [description]
   * @param string|null $referenceField [if defined, uses the field as reference during save]
   * @param array|null  $configOverride [allows overriding the configuration for the PKEY field]
   */
  protected function setPrimaryKey(string $name, ?string $referenceField = null, ?array $configOverride = null) {
    $this->primarykey = $name;
    if($referenceField) {
      $this->dataModelReferenceField = $referenceField;
    }
    if($configOverride) {
      $this->dataModelReferenceFieldConfigOverride = $configOverride;
    }
  }

  /**
   * if data model uses partitioning in some way
   * we can specify a reference field
   *
   * @var string|null
   */
  protected $dataModelReferenceField = null;

  /**
   * override some field config overrides
   * @var array|null
   */
  protected $dataModelReferenceFieldConfigOverride = null;

  /**
   * additional reference fields to be used
   * @var array
   */
  protected $dataModelAdditionalReferenceFields = [];

  /**
   * additional reference fields to be used
   * @var array
   */
  protected $dataModelAdditionalReferenceFieldConfigOverride = [];


  /**
   * enables setting an additional reference field
   * which may be a special reference field in the data model
   * @param string      $name           [description]
   * @param string|null $referenceField [if defined, uses the field as reference during save]
   * @param array|null  $configOverride [allows overriding the configuration for the PKEY field]
   */
  protected function addAdditionalReferenceField(string $name, ?string $referenceField = null, ?array $configOverride = null) {
    $this->dataModelAdditionalReferenceFields[$name] = $referenceField;
    if($configOverride) {
      $this->dataModelAdditionalReferenceFieldConfigOverride[$name] = $configOverride;
    }
  }

  /**
   * @inheritDoc
   */
  protected function loadConfig() : config
  {
    $components = $this->dynamicConfig->get();

    $config = [
      'field'     => [ $this->primarykey ],
      'primary'   => [ $this->primarykey ],
      'required'  => [],
      'foreign'   => [],
      'formConfigProvider' => [],
      'datatype'  => [],
    ];

    // add a foreign key
    if($this->dataModelReferenceField) {

      $fieldConfig = [
        'datatype'  => $this->dataModel->getConfig()->get('datatype>'.$this->dataModelReferenceField),
        // do not set FKEY. crud intervenes badly ATM.
        // 'foreign' => [
        //   'model'   => $this->dataModel->getIdentifier(),
        //   'schema'  => $this->dataModel->schema,
        //   'table'   => $this->dataModel->table,
        //   'key'     => $this->dataModelReferenceField,
        //   'display' => '{$element["'.$this->dataModelReferenceField.'"]}'
        // ]
      ];
      if($this->dataModelReferenceFieldConfigOverride) {
        $fieldConfig = array_replace_recursive($fieldConfig, $this->dataModelReferenceFieldConfigOverride);
      }

      $components = array_merge(
        [ $this->primarykey => $fieldConfig ],
        $components
      );

      // primary key can only be supplied here
      $config['primary'] = $this->primarykey;
    }

    // Additional reference fields
    if($this->dataModelAdditionalReferenceFields) {
      foreach($this->dataModelAdditionalReferenceFields as $field => $referenceField) {
        $fieldConfig = [
          'datatype'  => $this->dataModel->getConfig()->get('datatype>'.$referenceField),
          // do not set FKEY. crud intervenes badly ATM.
          // 'foreign' => [
          //   'model'   => $this->dataModel->getIdentifier(),
          //   'schema'  => $this->dataModel->schema,
          //   'table'   => $this->dataModel->table,
          //   'key'     => $this->dataModelReferenceField,
          //   'display' => '{$element["'.$this->dataModelReferenceField.'"]}'
          // ]
        ];
        if($configOverride = $this->dataModelAdditionalReferenceFieldConfigOverride[$field]) {
          $fieldConfig = array_replace_recursive($fieldConfig, $configOverride);
        }

        $components = array_merge(
          $components,
          [ $field => $fieldConfig ]
        );

        // primary key can only be supplied here
        // $config['primary'] = $this->primarykey;
      }
    }

    foreach($components as $key => $var) {
      // Add key as "field"
      $config['field'][] = $key;

      // Supply datatype
      if($var['datatype']) {
        $config['datatype'][$key] = $var['datatype'];
      } else {
        // error?
      }

      // required state
      if($var['required'] ?? false) {
        $config['required'][] = $key;
      }

      // optional
      if($var['foreign'] ?? false) {
        $config['foreign'][$key] = $var['foreign'];
      }

      // field handler
      if($var['field_handler'] ?? false) {
        $config['field_handler'][$key] = $var['field_handler'];
      }

      // formConfigProvider for a field
      // e.g
      // - "field": "..." (field reference used - FKEY needed)
      // - "inheritedClass" : "..." explicit value (class)
      //
      if($var['formConfigProvider'] ?? false) {
        $config['formConfigProvider'][$key] = $var['formConfigProvider'];
      }

      // form field config override(s)
      if($var['fieldconfig'] ?? false) {
        $config['fieldconfig'][$key] = $var['fieldconfig'];
      }
      // categorize
      $config['category'][$key] = $var['category'] ?? 'default'; // fallback!

      // TODO: editing rights?
    }

    $this->config = new \codename\core\config($config);
    return $this->config;
  }

  /**
   * field handlers per field
   * @var array|\codename\core\handler[]
   */
  protected $fieldHandlerInstances = [];

  /**
   * apply field handlers (if configured)
   * @param  string $field [description]
   * @param  mixed                          $value [description]
   * @param  array  $dataset
   * @return mixed                                 [description]
   */
  protected function applyFieldHandler($field, $value, array $dataset) {
    if($fieldHandlers = $this->config->get('field_handler>'.$field)) {
      foreach($fieldHandlers as $handlerName => $handlerConfig) {
        if(!isset($this->fieldHandlerInstances[$field][$handlerName])) {
          $class = app::getInheritedClass('handler_'.$handlerName);
          $handlerInstance = new $class($handlerConfig);
          $this->fieldHandlerInstances[$field][$handlerName] = $handlerInstance;
        }
        $value = $this->fieldHandlerInstances[$field][$handlerName]->handleValue($value, $dataset);
      }
    }
    return $value;
  }

  /**
   * get getOutput() values of field handlers
   *
   * @param  [type] $field   [description]
   * @param  [type] $value   [description]
   * @param  array  $dataset [description]
   * @return mixed          [description]
   */
  protected function getFieldHandlerOutput($field, $value, array $dataset) {
    if($fieldHandlers = $this->config->get('field_handler>'.$field)) {
      foreach($fieldHandlers as $handlerName => $handlerConfig) {
        if(!isset($this->fieldHandlerInstances[$field][$handlerName])) {
          $class = app::getInheritedClass('handler_'.$handlerName);
          $handlerInstance = new $class($handlerConfig);
          $this->fieldHandlerInstances[$field][$handlerName] = $handlerInstance;
        }
        $value = $this->fieldHandlerInstances[$field][$handlerName]->getOutput($value, $dataset);
      }
    }
    return $value;
  }

  /**
   * [getFieldHandlers description]
   * @param  [type] $field [description]
   * @return \codename\core\handler[]|null
   */
  public function getFieldHandlers($field) : ?array {
    if($fieldHandlers = $this->config->get('field_handler>'.$field)) {
      foreach($fieldHandlers as $handlerName => $handlerConfig) {
        if(!isset($this->fieldHandlerInstances[$field][$handlerName])) {
          $class = app::getInheritedClass('handler_'.$handlerName);
          $handlerInstance = new $class($handlerConfig);
          $this->fieldHandlerInstances[$field][$handlerName] = $handlerInstance;
        }
      }
    }
    return $this->fieldHandlerInstances[$field] ?? null;
  }

  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  protected function internalGetResult() : array
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * field of the data model used for identifying a value
   * @var string
   */
  protected $dataModelIdentifierField;

  /**
   * field of the data model providing the datatype
   * @var string
   */
  protected $dataModelDatatypeField;

  /**
   * field of the data model containing the value
   * @var string
   */
  protected $dataModelValueField;

  /**
   * define overrides that apply automatically to filter(s)
   * and values saved
   * @var array|null
   */
  protected $dataModelDatasetOverrides = null;

  /**
   * step needed for setting basic parameters
   * for handling the data model
   *
   * @param string $identifierField [description]
   * @param string $datatypeField   [description]
   * @param string $valueField      [description]
   */
  protected function setDataModelConfig(string $identifierField, string $datatypeField, string $valueField) {
    $this->dataModelIdentifierField = $identifierField;
    $this->dataModelDatatypeField = $datatypeField;
    $this->dataModelValueField = $valueField;
  }

  /**
   * [setDataModelDatasetOverrides description]
   * @param array $dataset [description]
   */
  protected function setDataModelDatasetOverrides(array $dataset) {
    if($this->dataModelDatasetOverrides) {
      throw new exception('DATAMODEL_DATASET_OVERRIDES_CAN_ONLY_BE_SET_ONCE', exception::$ERRORLEVEL_ERROR);
    }
    $this->dataModelDatasetOverrides = $dataset;

    if($this->dataModelDatasetOverrides) {
      foreach($this->dataModelDatasetOverrides as $field => $value) {
        $this->dataModel->addDefaultfilter($field, $value);
      }
    }
  }

  /**
   * returns the value for the given identifier - or null
   *
   * @param  string       $identifier          [description]
   * @param  mixed|null   $referenceFieldValue [description]
   * @return mixed|null
   */
  protected function getValue(string $identifier, $referenceFieldValue = null) {
    $cacheKey = $referenceFieldValue ?? 0;
    $dataset = null;

    if(!($this->datasetCache[$cacheKey] ?? false)) {
      if($this->dataModelReferenceField) {
        $this->dataModel->addFilter($this->dataModelReferenceField, $referenceFieldValue);
      }

      // TODO: additional reference fields

      // TODO: check for duplicates?

      //
      // retrieve all available datasets (using given reference field)
      // to avoid consecutive calls to this function
      // querying DB/data again and again
      //
      $res = $this->dataModel
        ->search()->getResult();

      // map identifier to key of to-be-cached resultset items
      $result = [];
      foreach($res as $r) {
        $result[$r[$this->dataModelIdentifierField]] = $r;
      }
      $this->datasetCache[$cacheKey] = $result;
    }

    return $this->datasetCache[$cacheKey][$identifier][$this->dataModelValueField] ?? null;
  }

  /**
   * contains in-memory stored entries used by ::getValue internally
   * @var array[]
   */
  protected $datasetCache = [];

  /**
   * @inheritDoc
   */
  public function search() : model
  {
    $dataset = [];
    foreach($this->getFields() as $field) {
      if(in_array($field, $this->hiddenFields)) {
        continue;
      }
      if($field !== $this->getPrimarykey()) {
        $val = $this->getValue($field, $this->primaryKeyFilterValue);
        if(array_key_exists($field, $this->filterValues)) {
          if($this->filterValues[$field] != $val) {
            $this->result = [];
            return $this;
          }
        }
        $dataset[$field] = $val;
      } else {
        $dataset[$this->getPrimarykey()] = $this->primaryKeyFilterValue;
      }
    }

    foreach($this->getFields() as $field) {
      if(in_array($field, $this->hiddenFields)) {
        continue;
      }
      $dataset[$field] = $this->getFieldHandlerOutput($field, $dataset[$field], $dataset);
    }

    $this->result = [$dataset];
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function reset()
  {
    parent::reset();
    // reset the special filter provided above
    $this->primaryKeyFilterValue = $this->primaryKeyDefaultFilterValue;
    $this->filterValues = $this->defaultFilterValues;
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
    $dataModel = $this->dataModel;

    // todo: dynamic transaction name based on this->getIdentifier?
    $transaction = new \codename\core\transaction($this->getIdentifier().'_data_save', [$dataModel]);
    $transaction->start();

    $invalidateFieldCaches = [];

    $useDataModelReferenceFieldValue = $this->dataModelReferenceField ? $data[$this->primarykey] : false;

    $useDataModelAdditionalReferenceFieldValues = count($this->dataModelAdditionalReferenceFields) > 0 ? array_intersect_key($data, array_flip($this->dataModelAdditionalReferenceFields)): false;

    // DEBUG
    // $saveCache = [];

    // open transaction?
    foreach($this->getFields() as $field) {
      //
      // NOTE/CHANGED 2019-11-07: filter for PKEY and additional reference fields (the latter one has been added)
      //
      if(array_key_exists($field, $data) && ($field !== $this->getPrimarykey()) && !(array_key_exists($field, $this->dataModelAdditionalReferenceFields))) {

        // TODO!
        $saveData = [
          $this->dataModelIdentifierField   => $field,
          $this->dataModelValueField        => $data[$field],
          $this->dataModelDatatypeField     => $this->getConfig()->get('datatype>'.$field)
        ];

        // overriding the pkey value
        if($useDataModelReferenceFieldValue !== false) {
          $saveData[$this->dataModelReferenceField] = $useDataModelReferenceFieldValue;
        }
        // overriding additional reference field values
        // here lies the DDD-magic (Domain Driven Design)
        if($useDataModelAdditionalReferenceFieldValues !== false) {
          $saveData = array_replace($saveData, $useDataModelAdditionalReferenceFieldValues);
        }

        $saveData[$this->dataModelValueField] = $this->applyFieldHandler($field, $saveData[$this->dataModelValueField], $data);

        // override/add some data provided via dataset overrides
        // e.g. fixed types
        if($this->dataModelDatasetOverrides) {
          $saveData = array_replace($saveData, $this->dataModelDatasetOverrides);
        }

        // try to get pkey (of dataModel - e.g. variable_id or portalsetting_id) and set, if defined (to allow create OR update)
        $dataModel->addFilter($this->dataModelIdentifierField, $field);

        // we have to filter it...
        if($useDataModelReferenceFieldValue !== false) {
          $dataModel->addFilter($this->dataModelReferenceField, $useDataModelReferenceFieldValue);
        }
        // filtering additional reference fields
        // here lies the DDD-magic (Domain Driven Design)
        if($useDataModelAdditionalReferenceFieldValues !== false) {
          foreach($useDataModelAdditionalReferenceFieldValues as $key => $value) {
            $dataModel->addFilter($key, $value);
          }
        }

        $dataset = $dataModel->search()->getResult()[0] ?? null;

        if($dataset) {
          $saveData[$dataModel->getPrimarykey()] = $dataset[$dataModel->getPrimarykey()];
        }

        // TEST: Skip saving null values
        if($dataset === null && $data[$field] === null) {
          continue;
        }

        // \codename\core\app::getResponse()->setData('DEBUG_SAVE_'.$field, $saveData);
        // continue;
        // return $this;

        // extend with some basic data?
        $dataModel->save($saveData);

        // DEBUG
        // $saveCache[] = $saveData;

        $invalidateFieldCaches[] = $field;
      }
    }

    // DEBUG!
    // \codename\core\app::getResponse()->setData('model_'.$this->getIdentifier().'_config', [
    //   '$this->dataModelReferenceField' => $this->dataModelReferenceField,
    //   '$data[$this->primarykey]' => $data[$this->primarykey],
    // ]);
    // \codename\core\app::getResponse()->setData('model_'.$this->getIdentifier().'_save_cache', $saveCache);

    $transaction->end();

    // reset cached values?
    $this->invalidateFieldCaches($invalidateFieldCaches);

    return $this;
  }

  /**
   * optional method cleaning separate caches, on demand.
   * @param string[] $fields
   */
  protected function invalidateFieldCaches($fields): void {
    return;
  }

  /**
   * [saveWithChildren description]
   * @param  array $data [description]
   * @return model       [description]
   */
  public function saveWithChildren(array $data) : model {
    return $this->save($data);
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
