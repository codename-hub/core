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
      return new \codename\core\config\json('config/model/' . $this->schema . '_' . $this->table . '.json', true, true);
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
     * @inheritDoc
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

      //
      // delay collection saving
      // we might need the pkey, if the base model entry is not yet created
      //
      $childCollectionSaves = [];

      // save children
      if($this->config->exists('children')) {
        foreach($this->config->get('children') as $child => $childConfig) {

          if($childConfig['type'] === 'foreign') {
            //
            // Foreign Key based child saving
            //

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
                } else {
                  $data2[$childConfig['field']] = $data[$child][$model->getPrimaryKey()];
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

          } else if($childConfig['type'] === 'collection') {
            //
            // Collection Saving of childs
            //

            // collection saving done below
            if(isset($this->collectionPlugins[$child]) && array_key_exists($child, $data)) {
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
      // Determine, if we're updating OR inserting (depending on PKEY value existance)
      //
      $update = (array_key_exists($this->getPrimarykey(), $data) && strlen($data[$this->getPrimarykey()]) > 0);
      if(!$update) {
        $data[$this->getPrimarykey()] = $this->lastInsertId();
      }

      //
      // Save child collections
      //
      if(count($childCollectionSaves) > 0) {
        foreach($childCollectionSaves as $child => $childData) {

          if($childData === null) {
            continue;
          }

          $collection = $this->collectionPlugins[$child];
          $model = $collection->collectionModel;

          // TODO: get all existing references/entries
          // that must be deleted/obsoleted
          $model->addFilter($collection->getCollectionModelBaseRefField(), $data[$collection->getBaseField()]);
          $existingCollectionItems = $model->search()->getResult();

          // determine must-have-pkeys
          $targetStateIds = array_reduce($childData, function($carry, $item) use ($model) {
            if($id = ($item[$model->getPrimaryKey()] ?? null)) {
              $carry[] = $id;
            }
            return $carry;
          }, []);

          // determine must-have-pkeys
          $existingIds = array_reduce($existingCollectionItems, function($carry, $item) use ($model) {
            if($id = ($item[$model->getPrimaryKey()] ?? null)) {
              $carry[] = $id;
            }
            return $carry;
          }, []);

          // the difference - to-be-deleted IDs
          $deleteIds = array_diff($existingIds, $targetStateIds);

          // delete them
          foreach($deleteIds as $id) {
            $model->delete($id);
          }

          foreach($childData as $childValue) {
            // TODO?: check for references!
            // For now, we're just overwriting the reference to THIS model / current dataset
            if(!isset($childValue[$collection->getCollectionModelBaseRefField()]) || ($childValue[$collection->getCollectionModelBaseRefField()] != $data[$collection->getBaseField()])) {
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

        // echo("<pre>" . print_r($result, true) . "</pre>");

        $tResult = $this->getVirtualFieldResult($result);

        // $fResult = [];
        //
        // //
        // // normalize
        // // TODO: Sibling Joins?
        // //
        // foreach($this->getNestedJoins() as $join) {
        //   // normalize using nested model - BUT: only if it's NOT already actively used as a child virtual field
        //   $found = false;
        //   if(($children = $this->config->get('children')) != null) {
        //     foreach($children as $field => $config) {
        //       if($config['type'] === 'foreign') {
        //         $foreign = $this->config->get('foreign>'.$config['field']);
        //         if($foreign['model'] === $join->model->getIdentifier()) {
        //           if($this->config->get('datatype>'.$field) == 'virtual') {
        //             $found = true;
        //             break;
        //           }
        //         }
        //       }
        //     }
        //   }
        //   if($found) {
        //     continue;
        //   }
        //
        //   foreach($tResult as $index => $r) {
        //     $fResult[$index] = array_merge(($fResult[$index] ?? []), $join->model->normalizeByFieldlist($r));
        //   }
        // }
        //
        // foreach($tResult as $index => $r) {
        //   // normalize using this model
        //   $fResult[$index] = array_merge(($fResult[$index] ?? []), $this->normalizeByFieldlist($r));
        // }
        //
        // $result = $fResult;


        $result = $this->normalizeRecursivelyByFieldlist($tResult);

        //
        // Root element virtual fields
        //
        if(count($this->virtualFields) > 0) {
          foreach($result as &$d) {
            // NOTE: at the moment, we already handle virtual fields
            // (e.g. a field added through ->addVirtualField)
            // in ->getVirtualFieldResult(...)
            // at the end, when we reached the original root structure again.
            // $d = $this->handleVirtualFields($d);
          }
        }

      }
      return $result;
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

        // // DEBUG
        // echo("<pre>Pre-Merge".chr(10));
        // print_r($fResult);
        // echo("</pre>");

        // echo("<pre>".print_r($normalized, true)."</pre>");

        // // METHOD 1: merge manually, row by row
        foreach($normalized as $index => $r) {
          // normalize using this model
          $fResult[$index] = array_merge(($fResult[$index] ?? []), $r);
        }

        // METHOD 2: recursive merge
        // NOTE: Actually, this doesn't work right.
        // It may split a model's result apart into two array elements in some cases.
        // $fResult = array_merge_recursive($fResult, $join->model->normalizeRecursivelyByFieldlist($result));

        // TESTING, OLD
        // foreach($fResult as $index => $r) {
          //   // $fResult[$index] = array_merge(($fResult[$index] ?? []), $join->model->normalizeByFieldlist($r));
          //   // $fResult[$index] = array_merge(($fResult[$index] ?? []), $join->model->normalizeRec($r));
        // }

        // // DEBUG
        // echo("<pre>Post-merge".chr(10));
        // print_r($fResult);
        // echo("</pre>");
      }

      //
      // Normalize using this model's fields
      //
      foreach($result as $index => $r) {
        // normalize using this model
        // CHANGED 2019-05-24: additionally call $this->normalizeRow around normalizeByFieldlist,
        // otherwise we might run into issues, e.g.
        // - "structure"-type fields are not json_decode'd, if present on the root model
        // - ... other things?
        $fResult[$index] = array_merge(($fResult[$index] ?? []), $this->normalizeRow($this->normalizeByFieldlist($r)));
      }

      // \codename\core\app::getResponse()->setData('model_normalize_debug', array_merge(\codename\core\app::getResponse()->getData('model_normalize_debug') ?? [], $fResult));

      // // DEBUG
      // echo("<pre>fResult:".chr(10));
      // print_r($fResult);
      // echo("</pre>");

      return $fResult;
    }

    /**
     * [normalizeByFieldlist description]
     * @param  array $dataset [description]
     * @return array          [description]
     */
    public function normalizeByFieldlist(array $dataset) : array {
      if(count($this->fieldlist) > 0) {
        // return $dataset;
        return array_intersect_key( $dataset, array_flip( array_merge( $this->getFieldlistArray($this->fieldlist), $this->getFields(), array_keys($this->virtualFields) ) ) );
      } else {
        // return $dataset;
        return array_intersect_key( $dataset, array_flip( array_merge( $this->getFields(), array_keys($this->virtualFields)) ) );
      }
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
    public function getVirtualFieldResult(array $result, &$track = [], array $structure = [], &$trackFields = []) {

      // DEBUG app::getResponse()->setData('structure', array_merge(app::getResponse()->getData('structure') ?? [], [$structure]));
      // DEBUG echo("structure1 ".implode('=>', $structure).'<br>');

      foreach($this->getNestedJoins() as $join) {
        $track[$join->model->getIdentifier()][] = $join->model;

        //
        // WORKAROUND 2019-06-17
        // for handling field indexes in joins
        // of same models, but differing fieldlists...
        //
        if($join->model instanceof \codename\core\model\schematic\sql) {
          $dummy = [];
          $vModelFieldlist = $join->model->getCurrentFieldlistNonRecursive(null, $dummy);

          // always pick the last array element
          foreach($vModelFieldlist as &$fieldComponents) {
            $fieldComponents = $fieldComponents[count($fieldComponents)-1];
          }

          $fields = [];

          if(in_array('*', $vModelFieldlist)) {
            foreach($join->model->getFields() as $field) {
              $fields[] = $field;
            }
          }

          $fields = array_merge($fields, $vModelFieldlist);

          foreach($fields as $field) {
            $trackFields[$field][] = $join->model;
          }
        }

        if($join->model instanceof \codename\core\model\virtualFieldResultInterface) {
          $structureDive = [];
          if(($children = $this->config->get('children')) != null) {
            foreach($children as $field => $config) {
              if($config['type'] === 'foreign') {
                $foreign = $this->config->get('foreign>'.$config['field']);
                if($this->config->get('datatype>'.$field) == 'virtual') {
                  if($join->modelField === $config['field']) {
                    $structureDive = [$field];
                  }
                }
              }
            }
          }
          $result = $join->model->getVirtualFieldResult($result, $track, array_merge($structure, $structureDive), $trackFields);
        }
      }

      // \codename\core\app::getResponse()->setData('sql_'.$this->getIdentifier(), $trackFields);
      // \codename\core\app::getResponse()->setData('sql_', $trackFields);
      // TODO, fix, refactor
      // foreach($this->getSiblingJoins() as $join) {
      //   $track[$join->model->getIdentifier()][] = $join->model;
      //   if($join->model instanceof \codename\core\model\virtualFieldResultInterface) {
      //     $result = $join->model->getVirtualFieldResult($result, $track, array_merge($structure, [$join->modelField]) );
      //   }
      // }

      // DEBUG echo("structure2 ".implode('=>', $structure).'<br>');

      if(($children = $this->config->get('children')) != null) {
        foreach($children as $field => $config) {
          if($config['type'] === 'foreign') {
            $foreign = $this->config->get('foreign>'.$config['field']);
            if($this->config->get('datatype>'.$field) == 'virtual') {
              if(!isset($track[$foreign['model']])) {
                $track[$foreign['model']] = [];
              }
              // $index = count($track[$foreign['model']])-1;
              $index = null;

              // DEBUG echo("Determining index for {$foreign['model']} ({$this->getIdentifier()}.{$field})...<br>");

              foreach($this->getNestedJoins() as $join) {
                if($join->modelField === $config['field']) {
                  if(count($indexes = array_keys($track[$foreign['model']], $join->model, true)) === 1) {
                    $index = $indexes[0];
                    // DEBUG echo("- index found: $index <br>");
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

              // DEBUG
              // $vModel->tIndex = $index;
              // $vModel->tIndexes = $indexes ?? null;
              // app::getResponse()->setData('fieldvModelIndex>'.$field, $index);

              // if((count($result) > 0) && $vModel !== null) {
              //   echo("Example source dataset entry: ".var_export($result[0][$vModel->getPrimaryKey()],true)."<br><br><br>");
              // }


              //
              // for sql-based models: extract fields
              // for others: set explicitly null
              //
              $vModelFieldlist = null;

              if($vModel instanceof \codename\core\model\schematic\sql) {
                $dummy = [];
                $vModelFieldlist = $vModel->getCurrentFieldlistNonRecursive(null, $dummy);
                // always pick the last array element
                foreach($vModelFieldlist as &$fieldComponents) {
                  $fieldComponents = $fieldComponents[count($fieldComponents)-1];
                }
              }

              foreach($result as &$dataset) {
                if($vModel != null) {

                  // //
                  // // Actually, we're checking for the existance of the primarykey value in a subset
                  // // which means, e.g.
                  // // dataset[some_primary_key] = [0] => ..., [1] => ...
                  // // if it's a joined result AND an array
                  // //
                  // // OR
                  // //
                  // // we simply check if its null
                  // //
                  // // Both cases mean: there's no subset.
                  // //
                  // if(is_array($dataset[$vModel->getPrimaryKey()]) && !isset($dataset[$vModel->getPrimaryKey()][$index])) {
                  //   $dataset[$field] = null;
                  //   continue;
                  // } else if(!is_array($dataset[$vModel->getPrimaryKey()]) && $dataset[$vModel->getPrimaryKey()] === null) {
                  //   $dataset[$field] = null;
                  //   continue;
                  // }

                  $vData = [];

                  // // DEBUG
                  // $vData['used_index'] = $index;
                  // $vData['used_rawdata'] = [];
                  // $vData['used_structure'] = $structure;
                  // echo("USED INDEX: ".$index ." for field $field<br><br><br>");

                  foreach($vModel->getFields() as $modelField) {

                    //
                    // handle the trackFields array from above
                    // which tracks possible PDO FETCH_NAMED indexes per field name
                    //
                    if($trackFields[$modelField] ?? false) {
                      // override index...
                      if(count($indexes = array_keys($trackFields[$modelField], $join->model, true)) === 1) {
                        $index = $indexes[0];
                      }
                    }

                    //
                    // we have to compare the fieldlist (actually enabled fields for display)
                    // either the field is mentioned explicitly in the query
                    // or we have a wildcard match for the current model (*)
                    //
                    if($vModelFieldlist !== null && !in_array($modelField, $vModelFieldlist) && !in_array('*', $vModelFieldlist)) {
                      continue;
                    }

                    //
                    // NOTE:
                    // We're looping through the fields of the virtual model
                    // if we detect a field being an array and NOT a virtual field by itself
                    // this is a PDO-Fetch-Named-Result specialty
                    // -> Join Results are being mapped onto an indexed array as the respective field
                    // Otherwise, we simply map the value provided.
                    //
                    // we used isset() around $dataset[$modelField]
                    // which was wrong - because isset evaluates to false
                    // even if the array value is NULL (which is, in fact, relevant for our framework)
                    // using it instead just to check for the presence of a value and then for the type (-> array & not virtual)
                    // otherwise, we do a ternary to work around nonexisting keys
                    //
                    if(isset($dataset[$modelField]) && is_array($dataset[$modelField]) && $vModel->getFieldtype(\codename\core\value\text\modelfield::getInstance($modelField)) !== 'virtual') {
                      $vData[$modelField] = $dataset[$modelField][$index] ?? null;
                    } else if(array_key_exists($modelField, $dataset)) {
                      //
                      // HACK/NOTE:
                      // this fixes the bug that leaves some 0 and 1 index keys in the resulting array
                      // possible explanation:
                      // If we set a null value at this stage, this gets merged (possibly recursively) with the real sub-result
                      // which causes the real result to appear AND additionally a null entry
                      //
                      // e.g.
                      // [field] => null
                      // later merge with [field] => [ 1, 2, 3 ]
                      // results in: [ 1, 2, 3, null ]
                      //
                      $vData[$modelField] = $dataset[$modelField]; //  ?? null;
                    }


                    // DEBUG
                    // $vData['used_rawdata'][$modelField] = $dataset[$modelField] ?? null;
                  }

                  $vData = $vModel->normalizeRow($vData);

                  // CHANGED 2019-06-05: moved to after-dive
                  // handle custom virtual fields
                  // if(count($vModel->getVirtualFields()) > 0) {
                  //   // foreach($vData as &$d) {
                  //   $vData = $vModel->handleVirtualFields($vData);
                  //   // }
                  // }

                  // Old method, put data to root array
                  // $dataset[$field] = $vData;

                  // new method: deep dive to set data
                  $dive = &$dataset;
                  foreach($structure as $key) {
                    $dive[$key] = $dive[$key] ?? [];
                    $dive = &$dive[$key];
                  }
                  $dive[$field] = array_merge_recursive($dive[$field] ?? [], $vData);

                  // handle custom virtual fields
                  // CHANGED 2019-06-05: we have to trigger virtual field handling
                  // AFTER diving, because we might be missing all the important fields...
                  if(count($vModel->getVirtualFields()) > 0) {
                    $dive[$field] = $vModel->handleVirtualFields($dive[$field]);
                  }

                  // DEBUG
                  // \codename\core\app::getResponse()->setData(
                  //   'dive_set',
                  //   array_merge(
                  //     \codename\core\app::getResponse()->getData('dive_set') ?? [],
                  //     [[
                  //       'structure' => $structure,
                  //       'field' => $field,
                  //       'data' => $vData
                  //     ]]
                  //   )
                  // );

                } else {
                  // app::getResponse()->setData('vModelIsNull>'.$this->getIdentifier(), [$foreign['model'], $index]);

                  // Old method, put data to root array
                  // $dataset[$field] = null;

                  // new method: deep dive to set data
                  $dive = &$dataset;
                  foreach($structure as $key) {
                    $dive[$key] = $dive[$key] ?? [];
                    $dive = &$dive[$key];
                  }
                  $dive[$field] = null; // array_merge_recursive($dive[$field] ?? [], $vData);

                  // DEBUG
                  // \codename\core\app::getResponse()->setData(
                  //   'dive_set',
                  //   array_merge(
                  //     \codename\core\app::getResponse()->getData('dive_set') ?? [],
                  //     [[
                  //       'structure' => $structure,
                  //       'field' => $field,
                  //       'data' => $vData
                  //     ]]
                  //   )
                  // );
                }
              }
            }
          // }
          } else if($config['type'] === 'collection') {

            // check for active collectionmodel / plugin
            if(isset($this->collectionPlugins[$field])) {
              $collection = $this->collectionPlugins[$field];
              $vModel = $collection->collectionModel;

              // determine to-be-used index for THIS model, as it is the base for the collection?
              // $index =
              $index = null;

              if((!isset($track[$this->getIdentifier()])) || count($track[$this->getIdentifier()]) === 0) {
                $index = null;
              } else {
                // foreach($this->getNestedJoins() as $join) {
                  // if($join->modelField === $config['field']) {
                    if(count($indexes = array_keys($track[$this->getIdentifier()], $this, true)) === 1) {
                      $index = $indexes[0];
                    }
                  // }
                // }
              }
              // if($index === null) {
              //   // err?
              // }

              foreach($result as &$dataset) {


                $filterValue = ($index !== null && is_array($dataset[$collection->getBaseField()])) ? $dataset[$collection->getBaseField()][$index] : $dataset[$collection->getBaseField()];


                $vModel->addFilter($collection->getCollectionModelBaseRefField(), $filterValue);
                $vResult = $vModel->search()->getResult();

                // // DEBUG
                // \codename\core\app::getResponse()->setData(
                //   'model_collection_debug',
                //   array_merge(
                //     \codename\core\app::getResponse()->getData('model_collection_debug') ?? [],
                //     [[
                //       'currentModelProcess' => $this->getIdentifier(),
                //       'filter with' => [
                //         $collection->getCollectionModelBaseRefField(),
                //         $dataset[$collection->getBaseField()],
                //       ],
                //       'filterValue' => $filterValue,
                //       'determination' => [
                //         'isset_' => isset($track[$this->getIdentifier()]),
                //         'count' => isset($track[$this->getIdentifier()]) ? count($track[$this->getIdentifier()]) : 'NOT SET',
                //         'indexes' => isset($track[$this->getIdentifier()]) ? array_keys($track[$this->getIdentifier()], $this, true) : 'NOT SET'
                //       ],
                //       'params' => [
                //         'track' => $track,
                //         'structure' => $structure,
                //         'collection' => [
                //           'getBaseField' => $collection->getBaseField(),
                //           'getCollectionModelBaseRefField' => $collection->getCollectionModelBaseRefField()
                //         ]
                //       ],
                //       'index' => $index,
                //       'testing',
                //       $dataset[$collection->getBaseField()],
                //       'vResult' => $vResult
                //     ]]
                //   )
                // );

                // new method: deep dive to set data
                $dive = &$dataset;
                foreach($structure as $key) {
                  $dive[$key] = $dive[$key] ?? [];
                  $dive = &$dive[$key];
                }
                $dive[$field] = $vResult;

                // OLD METHOD
                // $dataset[$field] = $vResult;
              }
            }
          }

          // TODO: Handle collections?
        }
      }


      // handle custom virtual fields
      if(count($structure) === 0) {
        if(count($this->virtualFields) > 0) {
          foreach($result as &$d) {
            $d = $this->handleVirtualFields($d);
          }
        }
      }
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
     * enables overriding/setting the connection
     * @param \codename\core\database $db [description]
     */
    public function setConnectionOverride(\codename\core\database $db) {
      $this->db = $db;
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
     * @inheritDoc
     */
    public function getCount(): int
    {
      //
      // Russian Caviar Begin
      // HACK/WORKAROUND for shrinking count-only-queries.
      //
      $this->countingModeOverride = true;

      $this->search();
      $count = $this->db->getResult()[0]['___count'];

      //
      // Russian Caviar End
      //
      $this->countingModeOverride = false;
      return $count;
    }

    /**
     * [private description]
     * @var bool
     */
    private $countingModeOverride = false;

    /**
     * @inheritDoc
     */
    public function reset()
    {
      if(!$this->countingModeOverride) {
        parent::reset();
      } else {
        // do not reset everything, if we're in special counting mode.
        // just reset errorstack.
        $this->errorstack->reset();
      }
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
        //
        // Contains Fix for JIRA [CODENAME-493]
        // see below. We include the main table here, from the start.
        // As it simply IS part of the used tables.
        //
        $tableUsage = [ "{$this->schema}.{$this->table}" => 1];
        $deepjoin = $this->deepJoin($this, $tableUsage);

        // prepare an array for values to submit as PDO statement parameters
        // done by-ref, so the values are arriving right here after
        // running getFilterQuery()
        $params = [];

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if($this->countingModeOverride) {
          $query .= 'COUNT('.$this->getPrimarykey().') as ___count';
        } else {
          // retrieve a list of all model field lists, recursively
          // respecting hidden fields and duplicate field names in other models/tables
          $fieldlist = $this->getCurrentFieldlist(null, $params);

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
        }

        $query .= ' FROM ' . $this->schema . '.' . $this->table . ' ';

        // append the previously constructed deepjoin string
        $query .= $deepjoin;

        //
        // Fix for JIRA [CODENAME-493]
        // Provide current main table specifier
        // as pseudo-alias
        // to fix multiple usage of the same model
        //
        $mainAlias = null;
        if($tableUsage["{$this->schema}.{$this->table}"] > 1) {
          $mainAlias = "{$this->schema}.{$this->table}";
        }

        $query .= $this->getFilterQuery($params, $mainAlias);

        $groups = $this->getGroups();
        if(count($groups) > 0) {
          $query .= ' GROUP BY '. implode(', ', $groups);
        }

        //
        // HAVING clause
        //
        // $appliedAggregateFilters = [];
        $aggregate = $this->getAggregateQueryComponents($params);
        if(count($aggregate) > 0) {
          $query .= ' HAVING '. self::convertFilterQueryArray($aggregate);
        }

        if(count($this->order) > 0) {
          $query .= $this->getOrders($this->order);
        }

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if(!$this->countingModeOverride) {
          if(!is_null($this->limit)) {
            $query .= $this->getLimit($this->limit);
          }

          if(!is_null($this->offset) > 0) {
            $query .= $this->getOffset($this->offset);
          }
        }

        //
        // Russian Caviar
        // HACK/WORKAROUND for shrinking count-only-queries.
        //
        if($this->countingModeOverride && count($groups) > 0) {
          $query = 'SELECT COUNT(___count) AS ___count FROM('.$query.') AS DerivedTableAlias';
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

        // TEMPORARY: SAVE LOG DISABLED
        // $this->saveLog('UPDATE', $data);

        //
        // disable cache reset, if model is not enabled for it.
        // At the moment, we don't even use the PRIMARY cache
        //
        if($this->cache) {
          $cacheGroup = $this->getCachegroup();
          $cacheKey = "PRIMARY_" . $data[$this->getPrimarykey()];
          $this->clearCache($cacheGroup, $cacheKey);
        }

        // raw data for usage with the timemachine
        if($this instanceof \codename\core\model\timemachineInterface && $this->isTimemachineEnabled()) {
          $raw = $data;
        }

        $query = 'UPDATE ' . $this->schema . '.' . $this->table .' SET ';
        $parts = [];

        foreach ($this->config->get('field') as $field) {
            if(in_array($field, array($this->getPrimarykey(), $this->table . "_modified", $this->table . "_created"))) {
                continue;
            }

            // If it exists, set the field
            if(array_key_exists($field, $data)) {

                if (is_object($data[$field]) || is_array($data[$field])) {
                    $data[$field] = $this->jsonEncode($data[$field]);
                }

                $var = $this->getStatementVariable(array_keys($param), $field);

                // performance hack: store modelfield instance!
                if(!isset($this->modelfieldInstance[$field])) {
                  $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
                }
                $fieldInstance = $this->modelfieldInstance[$field];

                $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                $parts[] = $field . ' = ' . ':'.$var;
            }
        }
        // NOTE: double entry, see line 1242
        $parts[] = $this->table . "_modified = now()";
        $query .= implode(',', $parts);

        $var = $this->getStatementVariable(array_keys($param), $this->getPrimarykey());
        // use timemachine, if capable and enabled
        // this stores delta values in a separate model

        // if ( ((new \ReflectionClass($this))->implementsInterface('\\codename\\core\\model\\timemachineInterface')
        if($this instanceof \codename\core\model\timemachineInterface && $this->isTimemachineEnabled()) {
          $tm = \codename\core\timemachine::getInstance($this->getIdentifier());
          $tm->saveState($data[$this->getPrimarykey()], $raw); // we have to use raw data, as we can't use jsonified arrays.
        }

        $param[$var] = $this->getParametrizedValue($data[$this->getPrimarykey()], 'number_natural'); // ? hardcoded type?

        $query .= " WHERE " . $this->getPrimarykey() . " = " . ':'.$var;
        return $query;

    }

    /**
     * [protected description]
     * @var \codename\core\value\text\modelfield[]
     */
    protected $modelfieldInstance = [];

    /**
     * returns a query that performs a save using INSERT
     * @param  array  $data     [data]
     * @param  array  &$param   [reference array that keeps track of PDO variable names]
     * @param  bool   $replace  [use replace on duplicate unique/pkey]
     * @return string           [query]
     */
    protected function saveCreate(array $data, array &$param = array(), bool $replace = false) {

        // TEMPORARY: SAVE LOG DISABLED
        // $this->saveLog('CREATE', $data);

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

                $var = $this->getStatementVariable(array_keys($param), $field);

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
                // if (is_object($data[$field]) || is_array($data[$field])) {
                //     $data[$field] = $this->jsonEncode($data[$field]);
                // }
                //
                // $var = $this->getStatementVariable(array_keys($param), $field);
                //
                // // performance hack: store modelfield instance!
                // if(!isset($this->modelfieldInstance[$field])) {
                //   $this->modelfieldInstance[$field] = \codename\core\value\text\modelfield::getInstance($field);
                // }
                // $fieldInstance = $this->modelfieldInstance[$field];
                //
                // $param[$var] = $this->getParametrizedValue($this->delimit($fieldInstance, $data[$field]), $this->getFieldtype($fieldInstance));
                // $parts[] = $field . ' = ' . ':'.$var;

                $parts[] = "{$field} = VALUES({$field})";
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
        } else if(($fieldtype === 'number_natural') || is_int($value)) {
          // NOTE: if integer value supplied, explicitly use this as param type
          $param = \PDO::PARAM_INT;
        } else if($fieldtype == 'boolean') {
          //
          // Temporary workaround for MySQL being so odd.
          // bool == tinyint(1) in MySQL-world. So, we pre-evaluate
          // the value to 0 or 1 (NULL being handled above)
          //
          $value = $value ? 1 : 0;
          $param = \PDO::PARAM_INT;
          // $param = \PDO::PARAM_BOOL;
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
        // if(strpos(get_class($this), 'activitystream') == false) {
        //     app::writeActivity("MODEL_" . $mode, get_class($this), $data);
        // }
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

              $var = $this->getStatementVariable(array_keys($param), $field);

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
      if($c === 0) {
        $name = preg_replace('/[^\w]+/', '_', $name);
      }
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
     * @param array       $filters            [array of filters]
     * @param array       $flagfilters        [array of flagfilters]
     * @param array       $filterCollections  [array of filter collections]
     * @param array       &$appliedFilters    [cross-model-instance array of currently applied filters, to keep track of PDO variables]
     * @param string|null $currentAlias       [current table alias provided during query time]
     * @return array
     */
    public function getFilters(array $filters = array(), array $flagfilters = array(), array $filterCollections = array(), array &$appliedFilters = array(), string $currentAlias = null) : array {

        $where = [];

        // Loop through each filter
        foreach($filters as $filter) {

            // collect data for a single filter
            $filterQuery = [
              'conjunction' => $filter->conjunction ?? $this->filterOperator,
              'query' => null
            ];

            if($filter instanceof \codename\core\model\plugin\filter\filterInterface) {
              // handle regular filters

              if(is_array($filter->value)) {
                  // filter value is an array (e.g. IN() match)
                  $values = array();
                  $i = 0;
                  foreach($filter->value as $thisval) {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->getFieldValue($currentAlias), $i++);
                      $values[] = ':' . $var; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field)); // values separated from query
                  }
                  $string = implode(', ', $values);
                  $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                  $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . $operator . ' ( ' . $string . ') ';
              } else {

                  // filter value is a singular value
                  // NOTE: $filter->value == 'null' (equality operator, compared to string) may evaluate to TRUE if you're passing in a positive boolean (!)
                  // instead, we're now using the identity operator === to explicitly check for a string 'null'
                  // NOTE: $filter->value == null (equality operator, compared to NULL) may evaluate to TRUE if you're passing in a negative boolean (!)
                  // instead, we're now using the identity operator === to explicitly check for a real NULL
                  // @see http://www.php.net/manual/en/types.comparisons.php
                  if(($filter->value === null) || (is_string($filter->value) && (strlen($filter->value) === 0)) || ($filter->value === 'null')) {
                      // $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' NULL'; // no param!
                      // $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                  } else {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->getFieldValue($currentAlias));
                      $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . $filter->operator . ' ' . ':'.$var.' '; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($filter->value, $this->getFieldtype($filter->field) ?? 'text'); // values separated from query
                  }
              }
            } else if($filter instanceof \codename\core\model\plugin\filterlist\filterlistInterface) {

              if(is_array($filter->value)) {
                  // filter value is an array (e.g. IN() match)
                  foreach($filter->value as $thisval) {
                    if(!is_numeric($thisval)) {
                      throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE, exception::$ERRORLEVEL_ERROR, $filter);
                    }
                  }
                  $string = implode(', ', $filter->value);
                  $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                  $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . $operator . ' (' . $string . ') ';
              } else {

                if(!preg_match('/^([0-9,]+)$/i',$filter->value)) {
                  throw new exception(self::EXCEPTION_SQL_GETFILTERS_INVALID_QUERY_VALUE, exception::$ERRORLEVEL_ERROR, $filter);
                }
                $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                $filterQuery['query'] = $filter->getFieldValue($currentAlias) . ' ' . $operator . ' (' . $filter->value . ') ';
              }

            } else if ($filter instanceof \codename\core\model\plugin\fieldfilter) {
              // handle field-based filters
              // this is not something PDO needs separately transmitted variables for
              // value IS indeed a field name
              // TODO: provide getFieldValue($tableAlias) also for fieldfilters
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
            'conjunction' => $flagfilter->conjunction ?? $this->filterOperator,
            'query' => null
          ];

          $flagVar1 = $this->getStatementVariable(array_keys($appliedFilters), $this->table.'_flag');
          $appliedFilters[$flagVar1] = null; // temporary dummy value
          $flagVar2 = $this->getStatementVariable(array_keys($appliedFilters), $this->table.'_flag');
          $appliedFilters[$flagVar2] = null; // temporary dummy value

          if($flagfilter < 0) {
            $filterQuery['query'] = $this->table.'_flag & ' . ':'.$flagVar1 . ' <> ' . ':'.$flagVar2 . ' '; // var = PDO Param
            $appliedFilters[$flagVar1] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
            $appliedFilters[$flagVar2] = $this->getParametrizedValue($flagfilter * -1, 'number_natural'); // values separated from query
          } else {
            $filterQuery['query'] = $this->table.'_flag & ' . ':'.$flagVar1 . ' = ' . ':'.$flagVar2 . ' '; // var = PDO Param
            $appliedFilters[$flagVar1] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
            $appliedFilters[$flagVar2] = $this->getParametrizedValue($flagfilter, 'number_natural'); // values separated from query
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
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->getFieldValue($currentAlias), $i++);
                      $values[] = ':' . $var; // var = PDO Param
                      $appliedFilters[$var] = $this->getParametrizedValue($this->delimit($filter->field, $thisval), $this->getFieldtype($filter->field));
                  }
                  $string = implode(', ', $values);
                  $operator = $filter->operator == '=' ? 'IN' : 'NOT IN';
                  $t_filter['query'] = $filter->getFieldValue($currentAlias) . ' ' . $operator . ' ( ' . $string . ') ';
              } else {
                  // value is a singular value
                  // NOTE: see other $filter->value == null (equality or identity operator) note and others
                  if($filter->value === null || (is_string($filter->value) && strlen($filter->value) == 0) || $filter->value === 'null') {
                      // $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->field->getValue());
                      $t_filter['query'] = $filter->getFieldValue($currentAlias) . ' ' . ($filter->operator == '!=' ? 'IS NOT' : 'IS') . ' NULL'; // var = PDO Param
                      // $appliedFilters[$var] = $this->getParametrizedValue(null, $this->getFieldtype($filter->field));
                  } else {
                      $var = $this->getStatementVariable(array_keys($appliedFilters), $filter->getFieldValue($currentAlias));
                      $t_filter['query'] = $filter->getFieldValue($currentAlias) . ' ' . $filter->operator . ' ' . ':'.$var.' ';
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
            'group_name' => $groupName,
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

        // // get filters from nested models recursively
        // foreach($this->nestedModels as $join) {
        //   if($this->compatibleJoin($join->model)) {
        //     $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters));
        //   }
        // }
        //
        // // get filters from sibling models recursively
        // foreach($this->siblingModels as $join) {
        //   if($this->compatibleJoin($join->model)) {
        //     $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters));
        //   }
        // }

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
     * @param  array        &$appliedFilters [reference array containing used filters]
     * @param  string|null  $mainAlias       [provide an alias for the main table]
     * @return string
     */
    public function getFilterQuery(array &$appliedFilters = array(), ?string $mainAlias = null) : string {

      // provide pseudo main table alias, if needed
      $filterQueryArray = $this->getFilterQueryComponents($appliedFilters, $mainAlias);


      //
      // HACK: re-join filter groups
      //
      $grouped = [];
      $ungrouped = [];
      foreach($filterQueryArray as $part) {
        if(is_array($part['query'])) {
          //
          // restructure part
          //
          $rePart = [
            'conjunction' => $part['conjunction'],
            'query' => []
          ];
          foreach($part['query'] as $queryComponent) {
            if($queryComponent['group_name'] ?? false) {
              $grouped[$queryComponent['group_name']] = $grouped[$queryComponent['group_name']] ?? [];
              $grouped[$queryComponent['group_name']]['group_name'] = $queryComponent['group_name'];
              $grouped[$queryComponent['group_name']]['conjunction'] = $queryComponent['conjunction']; // this resets it every time
              $grouped[$queryComponent['group_name']]['query'] = array_merge($grouped[$queryComponent['group_name']]['query'] ?? [], $queryComponent['query']);
            } else {
              $rePart['query'][] = $queryComponent;
            }
          }
          if(count($rePart['query']) > 0) {
            $ungrouped[] = $rePart;
          }
        } else {
          $ungrouped[] = $part;
        }
      }

      $filterQueryArray = array_merge($ungrouped, array_values($grouped));


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
     * @param  string|null $currentAlias
     * @return array
     */
    public function getFilterQueryComponents(array &$appliedFilters = array(), string $currentAlias = null) : array {
      $where = $this->getFilters($this->filter, $this->flagfilter, $this->filterCollections, $appliedFilters, $currentAlias);

      // get filters from nested models recursively
      foreach($this->nestedModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters, $join->currentAlias));
        }
      }

      // get filters from sibling models recursively
      foreach($this->siblingModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $where = array_merge($where, $join->model->getFilterQueryComponents($appliedFilters, $join->currentAlias));
        }
      }

      return $where;
    }

    /**
     * [getAggregateQueryComponents description]
     * @param  array &$appliedFilters [description]
     * @return array                 [description]
     */
    public function getAggregateQueryComponents(array &$appliedFilters = []) : array {
      $aggregate = $this->getFilters($this->aggregateFilter, [], [], $appliedFilters);

      // get filters from nested models recursively
      foreach($this->nestedModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $aggregate = array_merge($aggregate, $join->model->getAggregateQueryComponents($appliedFilters));
        }
      }

      // get filters from sibling models recursively
      foreach($this->siblingModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $aggregate = array_merge($aggregate, $join->model->getAggregateQueryComponents($appliedFilters));
        }
      }

      return $aggregate;
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
     * Returns grouping components
     * @author Kevin Dargel
     * @return array
     */
    public function getGroups(string $currentAlias = null) : array {
        $groupArray = [];

        // group by fields
        foreach($this->group as $group) {
            if($group->aliased) {
              $groupArray[] = $group->field->get();
            } else {
              if(!$currentAlias) {
                $groupArray[] = implode('.', array_filter([
                  $group->field->getSchema() ?? null,
                  $group->field->getTable() ?? null,
                  $group->field->get()
                ]));
              } else {
                $groupArray[] = implode('.', array_filter([
                  $currentAlias,
                  $group->field->get()
                ]));
              }
            }
        }

        foreach($this->getNestedJoins() as $join) {
          if($join->model instanceof \codename\core\model\schematic\sql) {
            $groupArray = array_merge($groupArray, $join->model->getGroups($join->currentAlias));
          }
        }

        //
        // TODO: sibling joins?
        //

        return $groupArray;
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
     * retrieves the fieldlist of this model
     * on a non-recursive basis
     *
     * @param  string|null  $alias    [description]
     * @param  array        &$params  [description]
     * @return array                  [description]
     */
    protected function getCurrentFieldlistNonRecursive(string $alias = null, array &$params) : array {
      $result = array();
      if(count($this->fieldlist) == 0 && count($this->hiddenFields) > 0) {
        //
        // Include all fields but specific ones
        //
        foreach($this->config->get('field') as $fieldName) {
          if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
            if(!in_array($fieldName, $this->hiddenFields)) {
              if($alias != null) {
                $result[] = array($alias, $fieldName);
              } else {
                $result[] = array($this->schema, $this->table, $fieldName);
              }
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
              // custom field calculation
              //
              $result[] = array($field->get());

            } else if($field instanceof \codename\core\model\plugin\aggregate\aggregateInterface) {

              //
              // pre-defined aggregate function
              //
              $result[] = array($field->get($alias));
            } else if($field instanceof \codename\core\model\plugin\fulltext\fulltextInterface) {

              //
              // pre-defined aggregate function
              //

              $var = $this->getStatementVariable(array_keys($params), $field->getField(), '_ft');
              $params[$var] = $this->getParametrizedValue($field->getValue(), 'text');
              $result[] = array($field->get($var, $alias));

            } else if($this->config->get('datatype>'.$field->field->get()) !== 'virtual' && (!in_array($field->field->get(), $this->hiddenFields) || $field->alias)) {
              //
              // omit virtual fields
              // they're not part of the DB.
              //
              $fieldAlias = $field->alias !== null ? $field->alias->get() : null;
              if($alias != null) {
                if($fieldAlias) {
                  $result[] = [ $alias, $field->field->get() . ' AS ' . $fieldAlias ];
                } else {
                  $result[] = [ $alias, $field->field->get() ];
                }
              } else {
                if($fieldAlias) {
                  $result[] = [ $field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table, $field->field->get() . ' AS ' . $fieldAlias ];
                } else {
                  $result[] = [ $field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table, $field->field->get() ];
                }
              }
            }
          }

          //
          // add the rest of the data-model-defined fields
          // as long as they're not hidden.
          //
          foreach($this->config->get('field') as $fieldName) {
            if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
              if(!in_array($fieldName, $this->hiddenFields)) {
                if($alias != null) {
                  $result[] = array($alias, $fieldName);
                } else {
                  $result[] = array($this->schema, $this->table, $fieldName);
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

        } else {
          //
          // No explicit fieldlist
          // No explicit hidden fields
          //
          if(count($this->hiddenFields) === 0) {
            //
            // The rest of the fields. Simply using a wildcard
            //
            if($alias != null) {
              $result[] = array($alias, '*');
            } else {
              $result[] = array($this->schema, $this->table, '*');
            }
          } else {
            // ugh?
          }
        }

      }

      return $result;
    }

    /**
     * Returns the current fieldlist as an array of triples (schema, table, field)
     * it contains the visible fields of all nested models (childs, siblings)
     * retrieved in a recursive call
     * this also respects hiddenFields
     *
     * @author Kevin Dargel
     * @param string|null  $alias   [optional: alias as prefix for the following fields - table alias!]
     * @param array &$params        [optional: current pdo params, including values]
     * @return array
     */
    protected function getCurrentFieldlist(string $alias = null, array &$params) : array {

      // CHANGED 2019-06-17: main functionality moved to ::getCurrentFieldlistNonRecursive
      // as we also need it for each model, singularly in ::getVirtualFieldResult()
      $result = $this->getCurrentFieldlistNonRecursive($alias, $params);

      foreach($this->nestedModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $result = array_merge($result, $join->model->getCurrentFieldlist($join->currentAlias, $params));
        }
      }
      foreach($this->siblingModels as $join) {
        if($this->compatibleJoin($join->model)) {
          $result = array_merge($result, $join->model->getCurrentFieldlist($join->currentAlias, $params));
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
