<?php
namespace codename\core\tests\model;

use codename\core\exception;

use codename\core\model\timemachineModelInterface;

use codename\core\tests\base;

/**
 * Base model class performing cross-platform/technology tests with model classes
 */
abstract class abstractModelTest extends base {

  /**
   * should return a database config for 'default' connection
   * @return array
   */
  protected abstract function getDefaultDatabaseConfig(): array;

  /**
   * [protected description]
   * @var bool
   */
  protected static $initialized = false;

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    static::deleteTestData();
    parent::tearDownAfterClass();
    static::$initialized = false;
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();
    $app->getAppstack();

    // avoid re-init
    if(static::$initialized) {
      return;
    }

    static::$initialized = true;

    static::setEnvironmentConfig([
      'test' => [
        'database' => [
          'default' => $this->getDefaultDatabaseConfig(),
        ],
        'cache' => [
          'default' => [
            'driver' => 'memory'
          ]
        ],
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'default' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);

    static::createModel('testschema', 'testdata', [
      'field' => [
        'testdata_id',
        'testdata_created',
        'testdata_modified',
        'testdata_datetime',
        'testdata_text',
        'testdata_date',
        'testdata_number',
        'testdata_integer',
        'testdata_boolean',
        'testdata_structure',
        'testdata_details_id',
      ],
      'primary' => [
        'testdata_id'
      ],
      'foreign' => [
        'testdata_details_id' => [
          'schema'  => 'testschema',
          'model'   => 'details',
          'key'     => 'details_id'
        ]
      ],
      'options' => [
        'testdata_number' => [
          'length'    => 16,
          'precision' => 8
        ]
      ],
      'datatype' => [
        'testdata_id'       => 'number_natural',
        'testdata_created'  => 'text_timestamp',
        'testdata_modified' => 'text_timestamp',
        'testdata_datetime' => 'text_timestamp',
        'testdata_details_id' => 'number_natural',
        'testdata_text'     => 'text',
        'testdata_date'     => 'text_date',
        'testdata_number'   => 'number',
        'testdata_integer'  => 'number_natural',
        'testdata_boolean'  => 'boolean',
        'testdata_structure'=> 'structure',
      ],
      'connection' => 'default'
    ]);

    static::createModel('testschema', 'details', [
      'field' => [
        'details_id',
        'details_created',
        'details_modified',
        'details_data',
        'details_virtual',
      ],
      'primary' => [
        'details_id'
      ],
      'datatype' => [
        'details_id'       => 'number_natural',
        'details_created'  => 'text_timestamp',
        'details_modified' => 'text_timestamp',
        'details_data'     => 'structure',
        'details_virtual'  => 'virtual',
      ],
      'connection' => 'default'
    ]);

    static::createModel('multi_fkey', 'table1', [
      'field' => [
        'table1_id',
        'table1_created',
        'table1_modified',
        'table1_key1',
        'table1_key2',
        'table1_value',
      ],
      'primary' => [
        'table1_id'
      ],
      'foreign' => [
        'multi_component_fkey' => [
          'schema'  => 'multi_fkey',
          'model'   => 'table2',
          'key'     => [
            'table1_key1' => 'table2_key1',
            'table1_key2' => 'table2_key2',
          ],
          'optional' => true
        ]
      ],
      'options' => [
        'table1_key1' => [
          'length'    => 16,
        ]
      ],
      'datatype' => [
        'table1_id'       => 'number_natural',
        'table1_created'  => 'text_timestamp',
        'table1_modified' => 'text_timestamp',
        'table1_modified' => 'text_timestamp',
        'table1_key1'     => 'text',
        'table1_key2'     => 'number_natural',
        'table1_value'    => 'text',
      ],
      'connection' => 'default'
    ]);
    static::createModel('multi_fkey', 'table2', [
      'field' => [
        'table2_id',
        'table2_created',
        'table2_modified',
        'table2_key1',
        'table2_key2',
        'table2_value',
      ],
      'primary' => [
        'table2_id'
      ],
      'options' => [
        'table2_key1' => [
          'length'    => 16,
        ]
      ],
      'datatype' => [
        'table2_id'       => 'number_natural',
        'table2_created'  => 'text_timestamp',
        'table2_modified' => 'text_timestamp',
        'table2_modified' => 'text_timestamp',
        'table2_key1'     => 'text',
        'table2_key2'     => 'number_natural',
        'table2_value'    => 'text',
      ],
      'connection' => 'default'
    ]);

    static::createModel('vfields', 'customer', [
      'field' => [
        'customer_id',
        'customer_created',
        'customer_modified',
        'customer_no',
        'customer_person_id',
        'customer_person',
        'customer_contactentries',
        'customer_notes',
      ],
      'primary' => [
        'customer_id'
      ],
      'unique' => [
        'customer_no',
      ],
      'required' => [
        'customer_no'
      ],
      'children' => [
        'customer_person' => [
          'type'  => 'foreign',
          'field' => 'customer_person_id'
        ],
        'customer_contactentries' => [
          'type'  => 'collection',
        ]
      ],
      'collection' => [
        'customer_contactentries' => [
          'schema'  => 'vfields',
          'model'   => 'contactentry',
          'key'     => 'contactentry_customer_id'
        ]
      ],
      'foreign' => [
        'customer_person_id' => [
          'schema'  => 'vfields',
          'model'   => 'person',
          'key'     => 'person_id'
        ]
      ],
      'options' => [
        'customer_no' => [
          'length' => 16
        ]
      ],
      'datatype' => [
        'customer_id'             => 'number_natural',
        'customer_created'        => 'text_timestamp',
        'customer_modified'       => 'text_timestamp',
        'customer_no'             => 'text',
        'customer_person_id'      => 'number_natural',
        'customer_person'         => 'virtual',
        'customer_contactentries' => 'virtual',
        'customer_notes'          => 'text',
      ],
      'connection' => 'default'
    ]);

    static::createModel('vfields', 'contactentry', [
      'field' => [
        'contactentry_id',
        'contactentry_created',
        'contactentry_modified',
        'contactentry_name',
        'contactentry_telephone',
        'contactentry_customer_id',
      ],
      'primary' => [
        'contactentry_id'
      ],
      'foreign' => [
        'contactentry_customer_id' => [
          'schema'  => 'vfields',
          'model'   => 'customer',
          'key'     => 'customer_id'
        ]
      ],
      'datatype' => [
        'contactentry_id'         => 'number_natural',
        'contactentry_created'    => 'text_timestamp',
        'contactentry_modified'   => 'text_timestamp',
        'contactentry_name'       => 'text',
        'contactentry_telephone'  => 'text_telephone',
        'contactentry_customer_id'=> 'number_natural',
      ],
      'connection' => 'default'
    ]);

    static::createModel('vfields', 'person', [
      'field' => [
        'person_id',
        'person_created',
        'person_modified',
        'person_firstname',
        'person_lastname',
        'person_birthdate',
        'person_country',
        'person_parent_id',
        'person_parent',
      ],
      'primary' => [
        'person_id'
      ],
      'children' => [
        'person_parent' => [
          'type'  => 'foreign',
          'field' => 'person_parent_id'
        ],
      ],
      'foreign' => [
        'person_parent_id' => [
          'schema'  => 'vfields',
          'model'   => 'person',
          'key'     => 'person_id'
        ],
        'person_country' => [
          'schema'  => 'json',
          'model'   => 'country',
          'key'     => 'country_code'
        ]
      ],
      'options' => [
        'person_country' => [
          'length' => 2
        ]
      ],
      'datatype' => [
        'person_id'         => 'number_natural',
        'person_created'    => 'text_timestamp',
        'person_modified'   => 'text_timestamp',
        'person_firstname'  => 'text',
        'person_lastname'   => 'text',
        'person_birthdate'  => 'text_date',
        'person_country'    => 'text',
        'person_parent_id'  => 'number_natural',
        'person_parent'     => 'virtual'
      ],
      'connection' => 'default'
    ]);

    static::createModel('json', 'country', [
      'field' => [
        'country_code',
        'country_name',
      ],
      'primary' => [
        'country_code'
      ],
      'datatype' => [
        'country_code' => 'text',
        'country_name' => 'text',
      ],
      // No connection, JSON datamodel
    ], function($schema, $model, $config) {
      return new \codename\core\tests\jsonModel(
        'tests/model/data/json_country.json',
        $schema,
        $model,
        $config
      );
    });

    static::createModel('timemachine', 'timemachine', [
      'field' => [
        'timemachine_id',
        'timemachine_created',
        'timemachine_modified',
        'timemachine_model',
        'timemachine_ref',
        'timemachine_data',
        'timemachine_source',
        'timemachine_user_id',
      ],
      'primary' => [
        'timemachine_id'
      ],
      'required' => [
        'timemachine_model',
        'timemachine_ref',
        'timemachine_data',
      ],
      'index' => [
        [ 'timemachine_model', 'timemachine_ref' ],
      ],
      'options' => [
        'timemachine_model' => [
          'length' => 64,
        ],
        'timemachine_ref' => [
          'db_column_type' => 'bigint',
        ],
        'timemachine_data' => [
          'db_column_type' => 'longtext',
        ],
      ],
      'datatype' => [
        'timemachine_id'        => 'number_natural',
        'timemachine_created'   => 'text_timestamp',
        'timemachine_modified'  => 'text_timestamp',
        'timemachine_model'     => 'text',
        'timemachine_ref'       => 'number_natural',
        'timemachine_data'      => 'structure',
        'timemachine_source'    => 'text',
        'timemachine_user_id'   => 'number_natural'
      ],
      'connection' => 'default'
    ]);


    static::architect('modeltest', 'codename', 'test');

    static::createTestData();
  }

  /**
   * Deletes data that is created during createTestData()
   */
  public static function deleteTestData(): void {
    $cleanupModels = [
      'testdata',
      'details',
      'timemachine',
      'table1',
      'table2',
    ];
    foreach($cleanupModels as $modelName) {
      $model = static::getModelStatic($modelName);
      $model->addFilter($model->getPrimarykey(), 0, '>')
        ->delete()->reset();

      // NOTE: we should not assert this in a static way
      // as it interferes with parallel or isolated test execution
      // and tests, that target doesNotPerformAssertions
      // static::assertEquals(0, $model->getCount());
    }
  }

  /**
   * [createTestData description]
   */
  protected static function createTestData(): void {

    // Just to make sure... initial cleanup
    // If there has been a shutdown failure after the last test
    // if this executed using a still running DB.
    static::deleteTestData();

    $testdataModel = static::getModelStatic('testdata');

    $entries = [
      [
        'testdata_text'     => 'foo',
        'testdata_datetime' => '2021-03-22 12:34:56',
        'testdata_date'     => '2021-03-22',
        'testdata_number'   => 3.14,
        'testdata_integer'  => 3,
        'testdata_structure'=> [ 'foo' => 'bar' ],
        'testdata_boolean'  => true,
      ],
      [
        'testdata_text'     => 'bar',
        'testdata_datetime' => '2021-03-22 12:34:56',
        'testdata_date'     => '2021-03-22',
        'testdata_number'   => 4.25,
        'testdata_integer'  => 2,
        'testdata_structure'=> [ 'foo' => 'baz' ],
        'testdata_boolean'  => true,
      ],
      [
        'testdata_text'     => 'foo',
        'testdata_datetime' => '2021-03-23 23:34:56',
        'testdata_date'     => '2021-03-23',
        'testdata_number'   => 5.36,
        'testdata_integer'  => 1,
        'testdata_structure'=> [ 'boo' => 'far' ],
        'testdata_boolean'  => false,
      ],
      [
        'testdata_text'     => 'bar',
        'testdata_datetime' => '2019-01-01 00:00:01',
        'testdata_date'     => '2019-01-01',
        'testdata_number'   => 0.99,
        'testdata_integer'  => 42,
        'testdata_structure'=> [ 'bar' => 'foo' ],
        'testdata_boolean'  => false,
      ],
    ];

    foreach($entries as $dataset) {
      $testdataModel->save($dataset);
    }
  }

  /**
   * [getDatabaseInstance description]
   * @param  array                   $config [description]
   * @return \codename\core\database         [description]
   */
  protected abstract function getDatabaseInstance(array $config): \codename\core\database;

  /**
   * [testSetConfigExplicitConnectionValid description]
   */
  public function testSetConfigExplicitConnectionValid(): void {
    $model = $this->getModel('testdata');
    $model->setConfig('default', 'testschema', 'testdata');

    $dataset = $model->setLimit(1)->search()->getResult()[0];
    $this->assertGreaterThanOrEqual(1, $dataset['testdata_id']);
  }

  /**
   * [testSetConfigExplicitConnectionInvalid description]
   */
  public function testSetConfigExplicitConnectionInvalid(): void {
    $this->expectException(\codename\core\exception::class);
    // TODO: right now we expect EXCEPTION_GETDATA_REQUESTEDKEYINTYPENOTFOUND message
    // but this might change soon
    $model = $this->getModel('testdata');
    $model->setConfig('nonexisting_connection', 'testschema', 'testdata');
  }

  /**
   * [testSetConfigInvalidValues description]
   */
  public function testSetConfigInvalidValues(): void {
    $this->expectException(\codename\core\exception::class);
    // TODO: specify the exception message
    $model = $this->getModel('testdata');
    $model->setConfig('default', 'nonexisting_schema', 'nonexisting_model');
  }

  /**
   * [testModelconfigInvalidWithoutCreatedAndModifiedField description]
   */
  public function testModelconfigInvalidWithoutCreatedAndModifiedField(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model\schematic\sql::EXCEPTION_MODEL_CONFIG_MISSING_FIELD);
    new \codename\core\tests\sqlModel('nonexisting', 'without_created_and_modified', [
      'field' => [
        'without_created_and_modified_id',
      ],
      'primary' => [
        'without_created_and_modified_id'
      ],
      'datatype' => [
        'without_created_and_modified_id' => 'number_natural',
      ]
    ]);
  }

  /**
   * [testModelconfigInvalidWithoutModifiedField description]
   */
  public function testModelconfigInvalidWithoutModifiedField(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model\schematic\sql::EXCEPTION_MODEL_CONFIG_MISSING_FIELD);
    new \codename\core\tests\sqlModel('nonexisting', 'without_modified', [
      'field' => [
        'without_modified_id',
        'without_modified_created',
      ],
      'primary' => [
        'without_modified_id'
      ],
      'datatype' => [
        'without_modified_id' => 'number_natural',
        'without_modified_created' => 'text_timestamp',
      ]
    ]);
  }

  /**
   * [testDeleteWithoutArgsWillFail description]
   */
  public function testDeleteWithoutArgsWillFail(): void {
    //
    // ::delete() without given PKEY, nor filters, MUST FAIL.
    //
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED');
    $model = $this->getModel('testdata');
    $model->delete();
  }

  /**
   * [testUpdateWithoutArgsWillFail description]
   */
  public function testUpdateWithoutArgsWillFail(): void {
    //
    // ::update() without filters MUST FAIL.
    //
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_UPDATE_NO_FILTERS_DEFINED');
    $model = $this->getModel('testdata');
    $model->update([
      'testdata_integer' => 0
    ]);
  }

  /**
   * [testAddCalculatedFieldExistsWillFail description]
   */
  public function testAddCalculatedFieldExistsWillFail(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model::EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS);
    $this->getModel('testdata')
      ->addCalculatedField('testdata_integer', '(1+1)');
  }

  /**
   * [testHideFieldSingle description]
   */
  public function testHideFieldSingle(): void {
    $model = $this->getModel('testdata');
    $fields = $model->getFields();

    $visibleFields = array_filter($fields, function($f) {
      return ($f != 'testdata_integer');
    });

    $model->hideField('testdata_integer');
    $res = $model->search()->getResult();

    $this->assertCount(4, $res);
    foreach($res as $r) {
      //
      // Make sure we don't get testdata_integer
      // but every other field
      //
      foreach($visibleFields as $f) {
        $this->assertArrayHasKey($f, $r);
      }
      $this->assertArrayNotHasKey('testdata_integer', $r);
    }
  }

  /**
   * [testHideFieldMultipleCommaTrim description]
   */
  public function testHideFieldMultipleCommaTrim(): void {
    $model = $this->getModel('testdata');
    $fields = $model->getFields();

    $visibleFields = array_filter($fields, function($f) {
      return ($f != 'testdata_integer') && ($f != 'testdata_text');
    });

    // Testing auto-split/explode and trim
    $model->hideField('testdata_integer, testdata_text');
    $res = $model->search()->getResult();

    $this->assertCount(4, $res);
    foreach($res as $r) {
      //
      // Make sure we don't get testdata_integer and testdata_text
      // but every other field
      //
      foreach($visibleFields as $f) {
        $this->assertArrayHasKey($f, $r);
      }
      $this->assertArrayNotHasKey('testdata_integer', $r);
      $this->assertArrayNotHasKey('testdata_text', $r);
    }
  }

  /**
   * [testHideAllFieldsAddOne description]
   */
  public function testHideAllFieldsAddOne(): void {
    $model = $this->getModel('testdata');
    $res = $model
      ->hideAllFields()
      ->addField('testdata_integer')
      ->search()->getResult();
    $this->assertCount(4, $res);
    foreach($res as $r) {
      // Make sure 'testdata_integer' is the one and only field in the result datasets
      $this->assertArrayHasKey('testdata_integer', $r);
      $this->assertEquals([ 'testdata_integer' ], array_keys($r));
    }
  }

  /**
   * Tests whether ::addField() works with comma-separated field names (string)
   */
  public function testHideAllFieldsAddMultiple(): void {
    $model = $this->getModel('testdata');
    $res = $model
      ->hideAllFields()
      ->addField('testdata_integer,testdata_text, testdata_number ') // internal trimming
      ->search()->getResult();
    $this->assertCount(4, $res);
    foreach($res as $r) {
      // Make sure 'testdata_integer' is the one and only field in the result datasets
      $this->assertArrayHasKey('testdata_integer', $r);
      $this->assertArrayHasKey('testdata_text', $r);
      $this->assertArrayHasKey('testdata_number', $r);
      $this->assertEquals([ 'testdata_integer', 'testdata_text', 'testdata_number' ], array_keys($r));
    }
  }

  /**
   * [testAddFieldFailsWithNonexistingField description]
   */
  public function testAddFieldFailsWithNonexistingField(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model::EXCEPTION_ADDFIELD_FIELDNOTFOUND);
    $model = $this->getModel('testdata');
    $model->addField('testdata_nonexisting'); // We expect an early failure
  }

  /**
   * [testAddFieldFailsWithMultipleFieldsAndAliasProvided description]
   */
  public function testAddFieldFailsWithMultipleFieldsAndAliasProvided(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_ADDFIELD_ALIAS_ON_MULTIPLE_FIELDS');
    $model = $this->getModel('testdata');
    $model->addField('testdata_integer,testdata_text', 'some_alias'); // Obviously, this is a no-go.
  }

  /**
   * [testHideAllFieldsAddAliasedField description]
   */
  public function testHideAllFieldsAddAliasedField(): void {
    $model = $this->getModel('testdata');
    $res = $model
      ->hideAllFields()
      ->addField('testdata_integer', 'aliased_field')
      ->search()->getResult();
    $this->assertCount(4, $res);
    foreach($res as $r) {
      // Make sure 'aliased_field' is the one and only field in the result datasets
      $this->assertArrayHasKey('aliased_field', $r);
      $this->assertEquals([ 'aliased_field' ], array_keys($r));
    }
  }

  /**
   * [testSimpleModelJoin description]
   */
  public function testSimpleModelJoin(): void {
    $model = $this->getModel('testdata')
      ->addModel($detailsModel = $this->getModel('details'));

    $originalDataset = [
      'testdata_number' => 3.3,
      'testdata_text'   => 'some_dataset',
    ];

    $detailsModel->save([
      'details_data' => $originalDataset,
    ]);
    $detailsId = $detailsModel->lastInsertId();

    $model->save(array_merge(
      $originalDataset, [ 'testdata_details_id' => $detailsId ]
    ));
    $id = $model->lastInsertId();

    $dataset = $model->load($id);
    $this->assertEquals($originalDataset, $dataset['details_data']);

    foreach($detailsModel->getFields() as $field) {
      if($detailsModel->getConfig()->get('datatype>'.$field) == 'virtual') {
        // In this case, no vfields/handler, expect it to NOT appear.
        $this->assertArrayNotHasKey($field, $dataset);
      } else {
        $this->assertArrayHasKey($field, $dataset);
      }
    }
    foreach($model->getFields() as $field) {
      $this->assertArrayHasKey($field, $dataset);
    }

    $model->delete($id);
    $detailsModel->delete($detailsId);
  }

  /**
   * [testSimpleModelJoinWithVirtualFields description]
   */
  public function testSimpleModelJoinWithVirtualFields(): void {
    $model = $this->getModel('testdata')->setVirtualFieldResult(true)
      ->addModel($detailsModel = $this->getModel('details'));

    $originalDataset = [
      'testdata_number' => 3.3,
      'testdata_text'   => 'some_dataset',
    ];

    $detailsModel->save([
      'details_data' => $originalDataset,
    ]);
    $detailsId = $detailsModel->lastInsertId();

    $model->save(array_merge(
      $originalDataset, [ 'testdata_details_id' => $detailsId ]
    ));
    $id = $model->lastInsertId();

    $dataset = $model->load($id);

    $this->assertEquals($originalDataset, $dataset['details_data']);

    foreach($detailsModel->getFields() as $field) {
      if($detailsModel->getConfig()->get('datatype>'.$field) == 'virtual') {
        // In this case, no vfields/handler, expect it to NOT appear.
        $this->assertArrayNotHasKey($field, $dataset);
      } else {
        $this->assertArrayHasKey($field, $dataset);
      }
    }
    foreach($model->getFields() as $field) {
      $this->assertArrayHasKey($field, $dataset);
    }

    // modify some model details
    $model->hideField('testdata_id');
    $detailsModel->hideField('details_created');
    $model->addField('testdata_id', 'root_level_alias');
    $detailsModel->addField('details_id', 'nested_alias');

    $dataset = $model->load($id);

    $this->assertArrayNotHasKey('testdata_id', $dataset);
    $this->assertArrayNotHasKey('details_created', $dataset);
    $this->assertArrayHasKey('root_level_alias', $dataset);
    $this->assertArrayHasKey('nested_alias', $dataset);

    $this->assertEquals($id, $dataset['root_level_alias']);
    $this->assertEquals($detailsId, $dataset['nested_alias']);

    $model->delete($id);
    $detailsModel->delete($detailsId);
  }

  /**
   * [testConditionalJoin description]
   * @return void
   */
  public function testConditionalJoin(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      );

    $customerIds = [];
    $personIds = [];

    $datasets = [
      [
        'customer_no' => 'A1000',
        'customer_person' => [
          'person_country'   => 'AT',
          'person_firstname' => 'Alex',
          'person_lastname'  => 'Anderson',
          'person_birthdate' => '1978-02-03',
        ],
      ],
      [
        'customer_no' => 'A1001',
        'customer_person' => [
          'person_country'   => 'AT',
          'person_firstname' => 'Bridget',
          'person_lastname'  => 'Balmer',
          'person_birthdate' => '1981-11-15',
        ],
      ],
      [
        'customer_no' => 'A1002',
        'customer_person' => [
          'person_country'   => 'DE',
          'person_firstname' => 'Christian',
          'person_lastname'  => 'Crossback',
          'person_birthdate' => '1990-04-19',
        ],
      ],
      [
        'customer_no' => 'A1003',
        'customer_person' => [
          'person_country'   => 'DE',
          'person_firstname' => 'Dodgy',
          'person_lastname'  => 'Data',
          'person_birthdate' => null,
        ],
      ]
    ];

    foreach($datasets as $d) {
      $customerModel->saveWithChildren($d);
      $customerIds[] = $customerModel->lastInsertId();
      $personIds[] = $personModel->lastInsertId();
    }

    // w/o model_name + double conditions
    $model = $this->getModel('customer')
      ->addCustomJoin(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_person_id',
        'person_id',
        [
          // will default to the higher-level model
          [ 'field' => 'customer_no', 'operator' => '>=', 'value' => '\'A1001\'' ],
          [ 'field' => 'customer_no', 'operator' => '<=', 'value' => '\'A1002\'' ],
        ]
      );
    $model->addOrder('customer_no', 'ASC'); // make sure to have the right order, see below
    $model->saveLastQuery = true;
    $res = $model->search()->getResult();
    $this->assertCount(4, $res);
    $this->assertEquals([null, 'AT', 'DE', null], array_column($res, 'person_country'));

    // using model_name
    $model = $this->getModel('customer')
      ->addCustomJoin(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_person_id',
        'person_id',
        [
          [ 'model_name' => 'person', 'field' => 'person_country', 'operator' => '=', 'value' => '\'DE\'' ],
        ]
      );
    $model->addOrder('customer_no', 'ASC'); // make sure to have the right order, see below
    $model->saveLastQuery = true;
    $res = $model->search()->getResult();
    $this->assertCount(4, $res);
    $this->assertEquals([null, null, 'DE','DE'], array_column($res, 'person_country'));

    // null value condition
    $model = $this->getModel('customer')
      ->addCustomJoin(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_person_id',
        'person_id',
        [
          [ 'model_name' => 'person', 'field' => 'person_birthdate', 'operator' => '=', 'value' => null ],
        ]
      );
    $model->addOrder('customer_no', 'ASC'); // make sure to have the right order, see below
    $model->saveLastQuery = true;
    $res = $model->search()->getResult();
    $this->assertCount(4, $res);
    $this->assertEquals([null, null, null,'DE'], array_column($res, 'person_country'));


    foreach($customerIds as $id) {
      $customerModel->delete($id);
    }
    foreach($personIds as $id) {
      $personModel->delete($id);
    }
  }

  /**
   * [testConditionalJoinFail description]
   */
  public function testConditionalJoinFail(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_JOIN_CONDITION_MODEL_NAME');
    $model = $this->getModel('customer')
      ->addCustomJoin(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_person_id',
        'person_id',
        [
          // non-associated model...
          [ 'model_name' => 'testdata', 'field' => 'testdata_number', 'operator' => '!=', 'value' => null ],
        ]
      );
    $model->addOrder('customer_no', 'ASC'); // make sure to have the right order, see below
    $model->saveLastQuery = true;
    $res = $model->search()->getResult();
  }

  /**
   * [testReverseJoin description]
   */
  public function testReverseJoinEquality(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      );

    $customerIds = [];
    $personIds = [];

    $datasets = [
      [
        'customer_no' => 'A1000',
        'customer_person' => [
          'person_country'   => 'AT',
          'person_firstname' => 'Alex',
          'person_lastname'  => 'Anderson',
          'person_birthdate' => '1978-02-03',
        ],
      ],
      [
        'customer_no' => 'A1001',
        'customer_person' => [
          'person_country'   => 'AT',
          'person_firstname' => 'Bridget',
          'person_lastname'  => 'Balmer',
          'person_birthdate' => '1981-11-15',
        ],
      ],
      [
        'customer_no' => 'A1002',
        'customer_person' => [
          'person_country'   => 'DE',
          'person_firstname' => 'Christian',
          'person_lastname'  => 'Crossback',
          'person_birthdate' => '1990-04-19',
        ],
      ],
      [
        'customer_no' => 'A1003',
        'customer_person' => [
          'person_country'   => 'DE',
          'person_firstname' => 'Dodgy',
          'person_lastname'  => 'Data',
          'person_birthdate' => null,
        ],
      ]
    ];

    foreach($datasets as $d) {
      $customerModel->saveWithChildren($d);
      $customerIds[] = $customerModel->lastInsertId();
      $personIds[] = $personModel->lastInsertId();
    }

    //
    // Create two models:
    // one      customer->person
    // and one  person->customer
    // - as long as we don't have much more data in it
    // this must match.
    // TODO: test multijoin aliases
    //
    $forwardJoinModel = $this->getModel('customer')
      ->addModel($this->getModel('person'));
    $resForward = $forwardJoinModel->search()->getResult();

    $reverseJoinModel = $this->getModel('person')
      ->addModel($this->getModel('customer'));
    $resReverse = $reverseJoinModel->search()->getResult();

    $this->assertCount(4, $resForward);
    $this->assertEquals($resForward, $resReverse);

    foreach($customerIds as $id) {
      $customerModel->delete($id);
    }
    foreach($personIds as $id) {
      $personModel->delete($id);
    }
  }

  /**
   * Tests replace() method of model (UPSERT)
   */
  public function testReplace(): void {
    $ids = [];
    $model = $this->getModel('customer');

    if(!($this instanceof \codename\core\tests\model\schematic\mysqlTest)) {
      $this->markTestIncomplete('Upsert is working differently on this platform - not implemented yet!');
    }

    $model->save([
      'customer_no'     => 'R1000',
      'customer_notes'  => 'Replace me'
    ]);
    $ids[] = $firstId = $model->lastInsertId();

    $model->replace([
      'customer_no'     => 'R1000',
      'customer_notes'  => 'Replaced'
    ]);

    $dataset = $model->load($firstId);
    $this->assertEquals('Replaced', $dataset['customer_notes']);

    foreach($ids as $id) {
      $model->delete($id);
    }
  }

  /**
   * [testMultiComponentForeignKeyJoin description]
   */
  public function testMultiComponentForeignKeyJoin(): void {
    $table1 = $this->getModel('table1');
    $table2 = $this->getModel('table2');

    $table1->save([
      'table1_key1'   => 'first',
      'table1_key2'   => 1,
      'table1_value'  => 'table1'
    ]);
    $table2->save([
      'table2_key1'   => 'first',
      'table2_key2'   => 1,
      'table2_value'  => 'table2'
    ]);
    $table1->save([
      'table1_key1'   => 'arbitrary',
      'table1_key2'   => 2,
      'table1_value'  => 'not in table2'
    ]);
    $table2->save([
      'table2_key1'   => 'arbitrary',
      'table2_key2'   => 3,
      'table2_value'  => 'not in table1'
    ]);

    $table1->addModel($table2);
    $res = $table1->search()->getResult();

    $this->assertCount(2, $res);
    $this->assertEquals('table1', $res[0]['table1_value']);
    $this->assertEquals('table2', $res[0]['table2_value']);

    $this->assertEquals('not in table2', $res[1]['table1_value']);
    $this->assertEquals(null, $res[1]['table2_value']);
  }

  /**
   * [testDeleteSinglePkeyTimemachineEnabled description]
   */
  public function testDeleteSinglePkeyTimemachineEnabled(): void {
    $model = $this->getTimemachineEnabledModel('testdata');
    $model->save([
      'testdata_text'     => 'single_pkey_delete',
      'testdata_integer'  => 1234,
    ]);
    $id = $model->lastInsertId();
    $this->assertNotEmpty($model->load($id));
    $model->delete($id);
    $this->assertEmpty($model->load($id));
  }

  /**
   * [testBulkUpdateAndDelete description]
   */
  public function testBulkUpdateAndDelete(): void {
    $model = $this->getModel('testdata');
    $this->testBulkUpdateAndDeleteUsingModel($model);
  }

  /**
   * [testBulkUpdateAndDeleteTimemachineEnabled description]
   */
  public function testBulkUpdateAndDeleteTimemachineEnabled(): void {
    $model = $this->getTimemachineEnabledModel('testdata');
    $this->testBulkUpdateAndDeleteUsingModel($model);
  }

  /**
   * [testBulkUpdateAndDeleteUsingModel description]
   * @param \codename\core\model $model [description]
   */
  protected function testBulkUpdateAndDeleteUsingModel(\codename\core\model $model): void {
    // $model = $this->getModel('testdata');

    // create example dataset
    $ids = [];
    for ($i=1; $i <= 10; $i++) {
      $model->save([
        'testdata_text'     => 'bulkdata_test',
        'testdata_integer'  => $i,
        'testdata_structure'=> [
          'some_key' => 'some_value',
        ]
      ]);
      $ids[] = $model->lastInsertId();
    }

    // update those entries (not by PKEY)
    $model
      ->addFilter('testdata_text', 'bulkdata_test')
      ->update([
        'testdata_integer'  => 333,
        'testdata_number'   => 12.34, // additional update data in this field not used before
        'testdata_structure'=> [
          'some_key'      => 'some_value',
          'some_new_key'  => 'some_new_value',
        ]
      ]);

    // compare data
    foreach($ids as $id) {
      $dataset = $model->load($id);
      $this->assertEquals('bulkdata_test', $dataset['testdata_text']);
      $this->assertEquals(333, $dataset['testdata_integer']);
    }

    // delete them
    $model
      ->addFilter($model->getPrimaryKey(), $ids)
      ->delete();

    // make sure they don't exist anymore
    $res = $model->addFilter($model->getPrimaryKey(), $ids)->search()->getResult();
    $this->assertEmpty($res);
  }

  /**
   * [testRecursiveModelJoin description]
   */
  public function testRecursiveModelJoin(): void {
    $personModel = $this->getModel('person');

    $datasets = [
      [
        // Top, no parent
        'person_firstname' => 'Ella',
        'person_lastname'  => 'Campbell',
      ],
      [
        // 1st level down
        'person_firstname' => 'Harry',
        'person_lastname'  => 'Sanders',
      ],
      [
        // 2nd level down
        'person_firstname' => 'Stephen',
        'person_lastname'  => 'Perkins',
      ],
      [
        // 3rd level down, no more childs
        'person_firstname' => 'Michael',
        'person_lastname'  => 'Vaughn',
      ],
    ];

    $ids = [];

    $parentId = null;
    foreach($datasets as $dataset) {
      $dataset['person_parent_id'] = $parentId;
      $personModel->save($dataset);
      $parentId = $personModel->lastInsertId();
      $ids[] = $personModel->lastInsertId();
    }

    $queryModel = $this->getModel('person')
      ->addRecursiveModel(
        $recursiveModel = $this->getModel('person')
          ->hideAllFields(),
        'person_parent_id',
        'person_id',
        [
          [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Vaughn' ]
        ],
        \codename\core\model\plugin\join::TYPE_INNER,
        'person_id',
        'person_parent_id'
      );
    $recursiveModel->addFilter('person_lastname', 'Sanders');
    $res = $queryModel->search()->getResult();
    $this->assertCount(1, $res);
    $this->assertEquals('Vaughn', $res[0]['person_lastname']);

    //
    // Joined traverse-up
    //
    $traverseUpModel = $this->getModel('person')
      ->hideAllFields()
      ->addRecursiveModel(
        $this->getModel('person'),
        'person_parent_id',
        'person_id',
        [
          // No anchor conditions
          // [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Vaughn' ]
        ],
        \codename\core\model\plugin\join::TYPE_INNER,
        'person_id',
        'person_parent_id'
      );
    $traverseUpModel->addFilter('person_lastname', 'Perkins');
    $res = $traverseUpModel->search()->getResult();

    $this->assertCount(3, $res);
    // NOTE: order is not guaranteed, therefore: just compare item presence
    $this->assertEqualsCanonicalizing([
      'Stephen',
      'Harry',
      'Ella',
    ], array_column($res, 'person_firstname'));

    //
    // Joined traverse-down
    //
    $traverseDownModel = $this->getModel('person')
      ->hideAllFields()
      ->addRecursiveModel(
        $this->getModel('person'),
        'person_id',
        'person_parent_id',
        [
          // No anchor conditions
          // e.g. [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Vaughn' ]
        ],
        \codename\core\model\plugin\join::TYPE_INNER,
        'person_id',
        'person_parent_id'
      );
    $traverseDownModel->addFilter('person_lastname', 'Perkins');
    $res = $traverseDownModel->search()->getResult();
    $this->assertCount(2, $res);
    // NOTE: order is not guaranteed, therefore: just compare item presence
    $this->assertEqualsCanonicalizing(['Stephen', 'Michael'], array_column($res, 'person_firstname'));

    //
    // Root-level traverse up
    //
    $rootTraverseUpModel = $this->getModel('person')
      ->setRecursive(
        'person_parent_id',
        'person_id',
        [
          // Single anchor condition
          [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
        ]
      );
    $res = $rootTraverseUpModel->search()->getResult();
    $this->assertCount(2, $res);
    // NOTE: order is not guaranteed, therefore: just compare item presence
    $this->assertEqualsCanonicalizing([ 'Harry', 'Ella' ], array_column($res, 'person_firstname'));

    //
    // Root-level traverse down
    //
    $rootTraverseDownModel = $this->getModel('person')
      ->setRecursive(
        'person_id',
        'person_parent_id',
        [
          // Single anchor condition
          [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
        ]
      );
    $res = $rootTraverseDownModel->search()->getResult();
    $this->assertCount(3, $res);
    // NOTE: order is not guaranteed, therefore: just compare item presence
    $this->assertEqualsCanonicalizing([ 'Harry', 'Stephen', 'Michael' ], array_column($res, 'person_firstname'));

    //
    // Root-level traverse down using filter instance
    //
    $rootTraverseDownUsingFilterInstanceModel = $this->getModel('person')
      ->setRecursive(
        'person_id',
        'person_parent_id',
        [
          // Single anchor condition, as filter plugin instance.
          // In this case, we use dynamic, just so we get a better compatibility
          // across differing drivers
          new \codename\core\model\plugin\filter\dynamic(\codename\core\value\text\modelfield::getInstance('person_lastname'), 'Sanders', '=')
        ]
      );
    $res = $rootTraverseDownUsingFilterInstanceModel->search()->getResult();
    $this->assertCount(3, $res);
    // NOTE: order is not guaranteed, therefore: just compare item presence
    $this->assertEqualsCanonicalizing([ 'Harry', 'Stephen', 'Michael' ], array_column($res, 'person_firstname'));

    //
    // Test joining a model that is used recursively
    //
    $joinedRecursiveModel = $this->getModel('person')
      ->hideAllFields()
      ->addField('person_id', 'main_id')
      ->addField('person_parent_id', 'main_parent')
      ->addField('person_firstname', 'main_firstname')
      ->addField('person_lastname', 'main_lastname')
      ->addModel(
        $this->getModel('person')
          // ->hideField('__anchor')
          ->setRecursive('person_parent_id', 'person_id', [
            // No filters in this case, we're just using an 'entry point' (Vaughn) below
            // [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
          ])
        , \codename\core\model\plugin\join::TYPE_INNER
        // , 'person_id'
        // , 'person_id'
      );

    $joinedRecursiveModel->addFilter('person_lastname', 'Vaughn' );
    $res = $joinedRecursiveModel->search()->getResult();
    $this->assertCount(3, $res);
    $this->assertEquals([ 'Vaughn' ], array_unique(array_column($res, 'main_lastname')));

    // NOTE: databases might behave differently regarding order
    //
    // e.g. SQLite: see https://www.sqlite.org/lang_with.html:
    // "If there is no ORDER BY clause, then the order in which rows are extracted is undefined."
    // SQLite is mostly doing FIFO.
    //
    $this->assertEqualsCanonicalizing([ 'Ella', 'Harry', 'Stephen' ], array_column($res, 'person_firstname'));

    foreach(array_reverse($ids) as $id) {
      $personModel->delete($id);
    }
  }

  /**
   * Tests whether calling setRecursive a second time will throw an exception
   */
  public function testSetRecursiveTwiceWillThrow(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SETRECURSIVE_ALREADY_ENABLED');

    $model = $this->getModel('person');
    for ($i=1; $i <= 2; $i++) {
      $model->setRecursive(
        'person_parent_id',
        'person_id',
        [
          // Single anchor condition
          [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
        ]
      );
    }
  }

  /**
   * Tests whether setRecursive will throw an exception
   * if an undefined relation is used as recursion parameter
   */
  public function testSetRecursiveInvalidConfigWillThrow(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_RECURSIVE_MODEL_CONFIG');

    $model = $this->getModel('person');
    $model->setRecursive(
      'person_firstname',
      'person_id',
      [
        // Single anchor condition
        [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
      ]
    );
  }

  /**
   * Tests whether setRecursive throws an exception
   * if a nonexisting field is provided in the configuration
   */
  public function testSetRecursiveNonexistingFieldWillThrow(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_RECURSIVE_MODEL_CONFIG');

    $model = $this->getModel('person');
    $model->setRecursive(
      'person_nonexisting',
      'person_id',
      [
        // Single anchor condition
        [ 'field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders' ]
      ]
    );
  }

  /**
   * [testFiltercollectionValueArray description]
   */
  public function testFiltercollectionValueArray(): void {

    // Filtercollection with an array as filter value
    // (e.g. IN-query)
    $model = $this->getModel('testdata');

    $model->addFiltercollection([
      [ 'field' => 'testdata_text', 'operator' => '=', 'value' => [ 'foo' ] ],
    ], 'OR');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);
    $this->assertEquals([3.14, 5.36], array_column($res, 'testdata_number'));

    $model->addFiltercollection([
      [ 'field' => 'testdata_text', 'operator' => '!=', 'value' => [ 'foo' ] ],
    ], 'OR');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);
    $this->assertEquals([4.25, 0.99], array_column($res, 'testdata_number'));
  }

  /**
   * [testDefaultFiltercollectionValueArray description]
   */
  public function testDefaultFiltercollectionValueArray(): void {
    // Filtercollection with an array as filter value
    // (e.g. IN-query)
    $model = $this->getModel('testdata');

    $model->addDefaultFilterCollection([
      [ 'field' => 'testdata_text', 'operator' => '=', 'value' => [ 'foo' ] ],
    ], 'OR');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);
    $this->assertEquals([3.14, 5.36], array_column($res, 'testdata_number'));

    // as we've added a default FC (and nothing else)
    // searching second time should yield the same resultset
    $this->assertEquals($res, $model->search()->getResult());
  }

  /**
   * Tests performing a regular left join
   * using forced virtual joining with no dataset available/set
   * to return a nulled/empty child dataset
   */
  public function testLeftJoinForcedVirtualNoReferenceDataset(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->setForceVirtualJoin(true),
      );

    $customerModel->saveWithChildren([
      'customer_no'     => 'join_fv_nochild',
      // No customer_person provided
    ]);

    $customerId = $customerModel->lastInsertId();

    // make sure to only find one result
    // (one entry that has both datasets)
    $dataset = $customerModel->load($customerId);

    $this->assertEquals('join_fv_nochild', $dataset['customer_no']);
    $this->assertNotEmpty($dataset['customer_person']);
    foreach($personModel->getFields() as $field) {
      if($personModel->getConfig()->get('datatype>'.$field) == 'virtual') {
        //
        // NOTE: we have no child models added
        // and we expect the result to NOT have those (virtual) fields at all
        //
        $this->assertArrayNotHasKey($field, $dataset['customer_person']);
      } else {
        // Expect the key(s) to exist, but be null.
        $this->assertArrayHasKey($field, $dataset['customer_person']);
        $this->assertNull($dataset['customer_person'][$field]);
      }
    }



    //
    // Test again using no VFR and varying FVJ states
    //
    $forceVirtualJoinStates = [ true, false ];

    foreach($forceVirtualJoinStates as $fvjState) {

      $noVfrCustomerModel = $this->getModel('customer')->setVirtualFieldResult(false)
        ->addModel(
          $noVfrPersonModel = $this->getModel('person')->setVirtualFieldResult(false)
            ->setForceVirtualJoin($fvjState),
        );

      $datasetNoVfr = $noVfrCustomerModel->load($customerId);

      $this->assertEquals('join_fv_nochild', $datasetNoVfr['customer_no']);
      $this->assertArrayNotHasKey('customer_person', $datasetNoVfr);
      foreach($noVfrPersonModel->getFields() as $field) {
        if($noVfrPersonModel->getConfig()->get('datatype>'.$field) == 'virtual') {
          //
          // NOTE: we have no child models added
          // and we expect the result to NOT have those (virtual) fields at all
          //
          $this->assertArrayNotHasKey($field, $datasetNoVfr);
        } else {
          // Expect the key(s) to exist, but be null.
          $this->assertArrayHasKey($field, $datasetNoVfr);
          $this->assertNull($datasetNoVfr[$field]);
        }
      }
    }


    $customerModel->delete($customerId);
  }

  /**
   * [testInnerJoinRegular description]
   */
  public function testInnerJoinRegular(): void {
    $this->testInnerJoin(false);
  }

  /**
   * [testInnerJoinForcedVirtualJoin description]
   */
  public function testInnerJoinForcedVirtualJoin(): void {
    $this->testInnerJoin(true);
  }

  /**
   * [testInnerJoin description]
   * @param bool $forceVirtualJoin [description]
   */
  protected function testInnerJoin(bool $forceVirtualJoin): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      );

    $customerIds = [];
    $personIds = [];

    $customerModel->saveWithChildren([
      'customer_no'     => 'join1',
      'customer_person' => [
        'person_firstname'  => 'Some',
        'person_lastname'   => 'Join',
      ]
    ]);

    $customerIds[] = $customerModel->lastInsertId();
    $personIds[] = $personModel->lastInsertId();

    $customerModel->saveWithChildren([
      'customer_no'     => 'join2',
      'customer_person' => null
    ]);

    $customerIds[] = $customerModel->lastInsertId();
    $personIds[] = $personModel->lastInsertId();

    $personModel->save([
      'person_firstname' => 'extra',
      'person_lastname' => 'person',
    ]);
    $personIds[] = $personModel->lastInsertId();

    $innerJoinModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $this->getModel('person')
          ->setVirtualFieldResult(true)
          ->setForceVirtualJoin($forceVirtualJoin),
        \codename\core\model\plugin\join::TYPE_INNER
      );

    // make sure to only find one result
    // (one entry that has both datasets)
    $innerJoinRes = $innerJoinModel->search()->getResult();
    $this->assertCount(1, $innerJoinRes);

    // compare to regular result (left join)
    $res = $customerModel->search()->getResult();
    $this->assertCount(2, $res);

    foreach($customerIds as $id) {
      $customerModel->delete($id);
    }
    foreach($personIds as $id) {
      $personModel->delete($id);
    }
  }

  /**
   * Tests a special situation:
   *
   * customer (model, vfr enabled)
   *  customer_person (vfield) displays:
   *  ->  person (model, joined)
   *       person_country (field) is join base for:
   *       -> country (model, bare join)
   *
   * if you hideAllFields in customer,
   * customer_person does not exist and neither does person_country
   * but the join is tried anyways.
   * We're throwing an exception this case,
   * as it is an indicator for incomplete code, missing definition
   * or even legacy code.
   */
  public function testJoinVirtualFieldResultEnabledMissingVKey(): void {
    $customerModel = $this->getModel('customer')
      ->setVirtualFieldResult(true)
      ->hideAllFields()
      ->addField('customer_no')
      ->addModel(
        $personModel = $this->getModel('person')
          ->addModel($this->getModel('country'))
      );

    $personModel->save([
      'person_firstname'  => 'john',
      'person_lastname'   => 'doe',
      'person_country'    => 'DE',
    ]);
    $personId = $personModel->lastInsertId();
    $customerModel->save([
      'customer_no' => 'missing_vkey',
      'customer_person_id' => $personId,
    ]);
    $customerId = $customerModel->lastInsertId();

    $dataset = $customerModel->load($customerId);
    $this->assertArrayHasKey('customer_person', $dataset);
    $this->assertEquals('john', $dataset['customer_person']['person_firstname']);
    $this->assertEquals('Germany', $dataset['customer_person']['country_name']);

    //
    // NOTE: this is still pending clearance. For now, this emulates the old behaviour.
    // VFR keys are added implicitly
    //
    // try {
    //   $dataset = $customerModel->load($customerId);
    //   $this->fail('Dataset loaded without exception to be fired - should crash.');
    // } catch (\codename\core\exception $e) {
    //   // NOTE: we only catch this specific exception!
    //   $this->assertEquals('EXCEPTION_MODEL_PERFORMBAREJOIN_MISSING_VKEY', $e->getMessage());
    // }

    $customerModel->delete($customerId);
    $personModel->delete($personId);
  }

  /**
   * [testJoinVirtualFieldResultEnabledCustomVKey description]
   */
  public function testJoinVirtualFieldResultEnabledCustomVKey(): void {
    $customerModel = $this->getModel('customer')
      ->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')
          ->addModel($this->getModel('country'))
      );

    $personModel->save([
      'person_firstname'  => 'john',
      'person_lastname'   => 'doe',
      'person_country'    => 'DE',
    ]);
    $personId = $personModel->lastInsertId();
    $customerModel->save([
      'customer_no' => 'missing_vkey',
      'customer_person_id' => $personId,
    ]);
    $customerId = $customerModel->lastInsertId();

    $customVKeyModel = $this->getModel('customer')
      ->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')
          ->addModel($this->getModel('country'))
      );

    // change the virtual field name of the join
    $join = $customVKeyModel->getNestedJoins('person')[0];
    $join->virtualField = 'custom_vfield';

    $dataset = $customVKeyModel->load($customerId);
    $this->assertArrayNotHasKey('customer_person', $dataset);
    $this->assertArrayHasKey('custom_vfield', $dataset);
    $this->assertEquals('john', $dataset['custom_vfield']['person_firstname']);
    $this->assertEquals('Germany', $dataset['custom_vfield']['country_name']);

    // NOTE: see testJoinVirtualFieldResultEnabledMissingVKey

    $customerModel->delete($customerId);
    $personModel->delete($personId);
  }

  /**
   * Tests a special case of model renormalization
   * no virtual field results enabled, two models on same nesting level (root)
   * with one or more hidden fields (each?)
   */
  public function testJoinHiddenFieldsNoVirtualFieldResult(): void {
    $customerModel = $this->getModel('customer')
      ->hideField('customer_no')
      ->addModel(
        $personModel = $this->getModel('person')
          ->hideField('person_firstname')
      );

    $personModel->save([
      'person_firstname'  => 'john',
      'person_lastname'   => 'doe',
    ]);
    $personId = $personModel->lastInsertId();
    $customerModel->save([
      'customer_no' => 'no_vfr',
      'customer_person_id' => $personId,
    ]);
    $customerId = $customerModel->lastInsertId();

    $dataset = $customerModel->load($customerId);
    $this->assertEquals('doe', $dataset['person_lastname']);
    $this->assertEquals($personId, $dataset['customer_person_id']);
    $this->assertArrayNotHasKey('person_firstname', $dataset);
    $this->assertArrayNotHasKey('customer_no', $dataset);

    $customerModel->delete($customerId);
    $personModel->delete($personId);
  }

  /**
   * Tests equally named fields in a joined model
   * to be re-normalized correctly
   * NOTE: this is SQL syntax and might be erroneous on non-sql models
   */
  public function testSameNamedCalculatedFieldsInVirtualFieldResults(): void {
    $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      ->addCalculatedField('calcfield', '(1+1)')
      ->addModel(
        $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->addCalculatedField('calcfield', '(2+2)')
      );

    $personModel->saveWithChildren([
      'person_firstname'  => 'theFirstname',
      'person_lastname'   => 'theLastName',
      'person_parent' => [
        'person_firstname'  => 'parentFirstname',
        'person_lastname'   => 'parentLastName',
      ]
    ]);

    $personId = $personModel->lastInsertId();
    $parentPersonId = $parentPersonModel->lastInsertId();

    $dataset = $personModel->load($personId);
    $this->assertEquals(2, $dataset['calcfield']);
    $this->assertEquals(4, $dataset['person_parent']['calcfield']);

    $personModel->delete($personId);
    $parentPersonModel->delete($parentPersonId);
  }

  /**
   * [testMixedModeVirtualFields description]
   */
  public function testRecursiveModelVirtualFieldDisabledWithAliasedFields(): void {
    $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      ->hideAllFields()
      ->addField('person_firstname')
      ->addField('person_lastname')
      ->addModel(
        // Parent optionally as forced virtual
        $parentPersonModel = $this->getModel('person')
          ->hideAllFields()
          ->addField('person_firstname', 'parent_firstname')
          ->addField('person_lastname', 'parent_lastname')
      );

    $personModel->saveWithChildren([
      'person_firstname'  => 'theFirstname',
      'person_lastname'   => 'theLastName',
      'person_parent' => [
        'person_firstname'  => 'parentFirstname',
        'person_lastname'   => 'parentLastName',
      ]
    ]);

    // NOTE: Important, disable for the following step.
    // (disabling vfields)
    $personModel->setVirtualFieldResult(false);

    $personId = $personModel->lastInsertId();
    $parentPersonId = $parentPersonModel->lastInsertId();

    $dataset = $personModel->load($personId);
    $this->assertEquals([
      'person_firstname'  => 'theFirstname',
      'person_lastname'   => 'theLastName',
      'parent_firstname'  => 'parentFirstname',
      'parent_lastname'   => 'parentLastName',
    ], $dataset);

    $personModel->delete($personId);
    $parentPersonModel->delete($parentPersonId);
  }

  /**
   * Tests a complex case of joining and model renormalization
   * (e.g. recursive models joined, but different fieldlists!)
   * In this case, a forced virtual join comes in-between.
   */
  public function testComplexVirtualRenormalizeForcedVirtualJoin(): void {
    $this->testComplexVirtualRenormalize(true);
  }

  /**
   * Tests a complex case of joining and model renormalization
   * (e.g. recursive models joined, but different fieldlists!)
   */
  public function testComplexVirtualRenormalizeRegular(): void {
    $this->testComplexVirtualRenormalize(false);
  }

  /**
   * [testComplexVirtualRenormalize description]
   * @param bool $forceVirtualJoin [description]
   */
  protected function testComplexVirtualRenormalize(bool $forceVirtualJoin): void {
    $personModel = $this->getModel('person')->setVirtualFieldResult(true)
      ->hideField('person_lastname')
      ->addModel(
        // Parent optionally as forced virtual
        $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->hideField('person_firstname')
          ->setForceVirtualJoin($forceVirtualJoin)
      );

    $personModel->saveWithChildren([
      'person_firstname'  => 'theFirstname',
      'person_lastname'   => 'theLastName',
      'person_parent' => [
        'person_firstname'  => 'parentFirstname',
        'person_lastname'   => 'parentLastName',
      ]
    ]);

    $personId = $personModel->lastInsertId();
    $parentPersonId = $parentPersonModel->lastInsertId();

    $dataset = $personModel->load($personId);

    $this->assertArrayNotHasKey('person_lastname', $dataset);
    $this->assertArrayNotHasKey('person_firstname', $dataset['person_parent']);

    // re-add the hidden fields aliased
    $personModel->addField('person_lastname', 'aliased_lastname');
    $parentPersonModel->addField('person_firstname', 'aliased_firstname');
    $dataset = $personModel->load($personId);
    $this->assertEquals('theLastName', $dataset['aliased_lastname']);
    $this->assertEquals('parentFirstname', $dataset['person_parent']['aliased_firstname']);

    // add the alias fields to the respective other models
    // (aliased vfield renormalization)
    $parentPersonModel->addField('person_lastname', 'aliased_lastname');
    $personModel->addField('person_firstname', 'aliased_firstname');
    $dataset = $personModel->load($personId);
    $this->assertEquals('theFirstname', $dataset['aliased_firstname']);
    $this->assertEquals('theLastName', $dataset['aliased_lastname']);
    $this->assertEquals('parentFirstname', $dataset['person_parent']['aliased_firstname']);
    $this->assertEquals('parentLastName', $dataset['person_parent']['aliased_lastname']);

    $personModel->delete($personId);
    $parentPersonModel->delete($parentPersonId);
  }

  /**
   * [testComplexJoin description]
   */
  public function testComplexJoin(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->addVirtualField('person_fullname1', function($dataset) {
            return $dataset['person_firstname'].' '.$dataset['person_lastname'];
          })
          ->addModel($this->getModel('country'))
          ->addModel(
            // Parent as forced virtual
            $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
              ->addVirtualField('person_fullname2', function($dataset) {
                return $dataset['person_firstname'].' '.$dataset['person_lastname'];
              })
              ->setForceVirtualJoin(true)
              ->addModel($this->getModel('country'))
          )
      )
      ;

    $customerModel->saveWithChildren([
      'customer_no'     => 'COMPLEX1',
      'customer_person' => [
        'person_firstname'  => 'Johnny',
        'person_lastname'   => 'Doenny',
        'person_birthdate'  => '1950-04-01',
        'person_country'    => 'AT',
        'person_parent'     => [
          'person_firstname'  => 'Johnnys',
          'person_lastname'   => 'Father',
          'person_birthdate'  => '1930-12-10',
          'person_country'    => 'DE',
        ]
      ]
    ]);

    $customerId = $customerModel->lastInsertId();
    $personId = $personModel->lastInsertId();
    $parentPersonId = $parentPersonModel->lastInsertId();

    $dataset = $customerModel->load($customerId);

    $this->assertEquals('COMPLEX1', $dataset['customer_no']);
    $this->assertEquals('Doenny', $dataset['customer_person']['person_lastname']);
    $this->assertEquals('Austria', $dataset['customer_person']['country_name']);
    $this->assertEquals('Father', $dataset['customer_person']['person_parent']['person_lastname']);
    $this->assertEquals('Germany', $dataset['customer_person']['person_parent']['country_name']);

    $this->assertEquals('Johnny Doenny', $dataset['customer_person']['person_fullname1']);
    $this->assertEquals('Johnnys Father', $dataset['customer_person']['person_parent']['person_fullname2']);

    // make sure there are no other fields on the root level
    $intersect = array_intersect(array_keys($dataset), $customerModel->getFields());
    $this->assertEmpty(array_diff(array_keys($dataset), $intersect));

    $customerModel->delete($customerId);
    $personModel->delete($personId);
    $parentPersonModel->delete($parentPersonId);
  }

  /**
   * Joins a model (itself) recursively (as far as possible)
   * @param string $modelName           [model used for joining recursively]
   * @param int    $limit               [amount of joins performed]
   * @param bool   $virtualFieldResult  [whether to switch on vFieldResults by default]
   * @return \codename\core\model
   */
  protected function joinRecursively(string $modelName, int $limit, bool $virtualFieldResult = false): \codename\core\model {
    $model = $this->getModel($modelName)->setVirtualFieldResult($virtualFieldResult);
    $currentModel = $model;
    for ($i=0; $i < $limit; $i++) {
      $recurseModel = $this->getModel($modelName)->setVirtualFieldResult($virtualFieldResult);
      $currentModel->addModel($recurseModel);
      $currentModel = $recurseModel;
    }
    return $model;
  }

  /**
   * [testJoinNestingLimitExceededWillFail description]
   */
  public function testJoinNestingLimitExceededWillFail(): void {
    $this->expectException(\PDOException::class);
    // exhaust the join nesting limit
    $model = $this->joinRecursively('person', $this->getJoinNestingLimit());
    $model->search()->getResult();
  }

  /**
   * [testJoinNestingLimitMaxxedOut description]
   */
  public function testJoinNestingLimitMaxxedOut(): void {
    $this->expectNotToPerformAssertions();
    // Try to max-out the join nesting limit (limit - 1)
    $model = $this->joinRecursively('person', $this->getJoinNestingLimit() - 1);
    $model->search()->getResult();
  }

  /**
   * [testJoinNestingLimitMaxxedOutSaving description]
   */
  public function testJoinNestingLimitMaxxedOutSaving(): void {
    $this->testJoinNestingLimit();
  }

  /**
   * [testJoinNestingBypassLimitation1 description]
   */
  public function testJoinNestingBypassLimitation1(): void {
    $this->testJoinNestingLimit(1);
  }

  /**
   * [testJoinNestingBypassLimitation2 description]
   */
  public function testJoinNestingBypassLimitation2(): void {
    $this->testJoinNestingLimit(2);
  }

  /**
   * [testJoinNestingBypassLimitation3 description]
   */
  public function testJoinNestingBypassLimitation3(): void {
    $this->testJoinNestingLimit(3);
  }


  /**
   * [testJoinNestingLimit description]
   * @param int|null $exceedLimit [description]
   */
  protected function testJoinNestingLimit(?int $exceedLimit = null): void {

    $limit = $this->getJoinNestingLimit() - 1;

    $model = $this->joinRecursively('person', $limit, true);

    $deeperModel = null;
    if($exceedLimit) {
      $currentJoin = $model->getNestedJoins('person')[0] ?? null;
      $deeplyNestedJoin = $currentJoin;
      while($currentJoin !== null) {
        $currentJoin = $currentJoin->model->getNestedJoins('person')[0] ?? null;
        if($currentJoin) {
          $deeplyNestedJoin = $currentJoin;
        }
      }
      $deeperModel = $this->getModel('person')
        ->setVirtualFieldResult(true)
        ->setForceVirtualJoin(true);

      $deeplyNestedJoin->model
        ->addModel(
          $deeperModel
        );

      if($exceedLimit > 1) {
        // NOTE: joinRecursively returns at least 1 model instance
        // as we already have one above, we now have to reduce by 2 (!)
        $evenDeeperModel = $this->joinRecursively('person', $exceedLimit-2, true);
        $deeperModel->addModel($evenDeeperModel);
      }
      $limit += $exceedLimit;
    }


    $dataset = null;
    $savedExceeded = 0;

    // $maxI = $limit + 1;
    foreach(range($limit + 1, 1) as $i) {
      $dataset = [
        'person_firstname' => 'firstname'.$i,
        'person_lastname'  => 'testJoinNestingLimitMaxxedOutSaving',
        'person_parent'    => $dataset,
      ];
      if($exceedLimit && ($i > ($limit-$exceedLimit+1))) {
        $dataset['person_country'] = 'DE';
        $savedExceeded++;
      }
    }

    $model->saveWithChildren($dataset);

    $id = $model->lastInsertId();

    $loadedDataset = $model->load($id);

    // if we have a deeper model joined
    // (see above) we verify we have those tiny modifications
    // successfully saved
    if($deeperModel) {
      $deeperId = $deeperModel->lastInsertId();
      $deeperDataset = $deeperModel->load($deeperId);

      // print_r($deeperDataset);
      $this->assertEquals($exceedLimit, $savedExceeded);

      $diveDataset = $deeperDataset;
      for ($i=0; $i < $savedExceeded; $i++) {
        $this->assertEquals('DE', $diveDataset['person_country']);
        $diveDataset = $diveDataset['person_parent'];
      }
    }

    $this->assertEquals('firstname1', $loadedDataset['person_firstname']);

    foreach(range(0, $limit) as $l) {
      $path = array_fill(0, $l,'person_parent');
      $childDataset = \codename\core\helper\deepaccess::get($dataset, $path);
      $this->assertEquals('firstname'.($l + 1), $childDataset['person_firstname']);
    }

    $cnt = $this->getModel('person')
      ->addFilter('person_lastname', 'testJoinNestingLimitMaxxedOutSaving')
      ->getCount();
    $this->assertEquals($limit + 1, $cnt);

    $this->getModel('person')
      ->addDefaultfilter('person_lastname','testJoinNestingLimitMaxxedOutSaving')
      ->update([
        'person_parent_id' => null
      ])
      ->delete();
  }

  /**
   * Maximum (expected) join limit
   * @return int [description]
   */
  protected abstract function getJoinNestingLimit(): int;

  /**
   * [testGetCount description]
   */
  public function testGetCount(): void {
    $model = $this->getModel('testdata');

    $this->assertEquals(4, $model->getCount());

    $model->addFilter('testdata_text', 'bar');
    $this->assertEquals(2, $model->getCount());

    // Test model getCount() to _NOT_ reset filters
    $this->assertEquals(2, $model->getCount());

    // Explicit reset
    $model->reset();
    $this->assertEquals(4, $model->getCount());
  }

  /**
   * [testAddModelExplicitModelfieldValid description]
   */
  public function testAddModelExplicitModelfieldValid(): void {
    $saveCustomerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel($savePersonModel = $this->getModel('person'));
    $saveCustomerModel->saveWithChildren([
      'customer_no' => 'ammv',
      'customer_person' => [
        'person_firstname' => 'ammv1',
        'person_firstname' => 'ammv2',
      ]
    ]);
    $customerId = $saveCustomerModel->lastInsertId();
    $personId = $savePersonModel->lastInsertId();


    $model = $this->getModel('customer')
      ->addModel(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_person_id'
      );

    $res = $model->search()->getResult();
    $this->assertCount(1, $res);

    // TODO: detail data tests?

    $saveCustomerModel->delete($customerId);
    $savePersonModel->delete($personId);
    $this->assertEmpty($savePersonModel->load($personId));
    $this->assertEmpty($saveCustomerModel->load($customerId));
  }

  /**
   * [testAddModelExplicitModelfieldInvalid description]
   */
  public function testAddModelExplicitModelfieldInvalid(): void {
    //
    // Try to join on a field that's not designed for it
    //
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION');

    $model = $this->getModel('customer')
      ->addModel(
        $this->getModel('person'),
        \codename\core\model\plugin\join::TYPE_LEFT,
        'customer_no' // invalid field for this model
      );
  }

  /**
   * [testAddModelInvalidNoRelation description]
   */
  public function testAddModelInvalidNoRelation(): void {
    //
    // Try to join a model that has no relation to it
    //
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION');

    $model = $this->getModel('testdata')
      ->addModel(
        $this->getModel('person')
      );
  }

  /**
   * [testVirtualFieldSaving description]
   */
  public function testVirtualFieldResultSaving(): void {

    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel(
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->addModel($parentPersonModel = $this->getModel('person'))
      )
      ->addCollectionModel($contactentryModel = $this->getModel('contactentry'));

    $dataset = [
      'customer_no' => 'K1000',
      'customer_person' => [
        'person_firstname'  => 'John',
        'person_lastname'   => 'Doe',
        'person_birthdate'  => '1970-01-01',
        'person_parent' => [
          'person_firstname'  => 'Maria',
          'person_lastname'   => 'Ada',
          'person_birthdate'  => null,
        ]
      ],
      'customer_contactentries' => [
        [ 'contactentry_name' => 'Phone', 'contactentry_telephone' => '+49123123123' ]
      ]
    ];

    $this->assertTrue($customerModel->isValid($dataset));

    $customerModel->saveWithChildren($dataset);

    $customerId = $customerModel->lastInsertId();
    $personId = $personModel->lastInsertId();
    $parentPersonId = $parentPersonModel->lastInsertId();

    $dataset = $customerModel->load($customerId);

    $this->assertEquals('K1000', $dataset['customer_no']);
    $this->assertEquals('John', $dataset['customer_person']['person_firstname']);
    $this->assertEquals('Doe', $dataset['customer_person']['person_lastname']);
    $this->assertEquals('Phone', $dataset['customer_contactentries'][0]['contactentry_name']);
    $this->assertEquals('+49123123123', $dataset['customer_contactentries'][0]['contactentry_telephone']);

    $this->assertEquals('Maria', $dataset['customer_person']['person_parent']['person_firstname']);
    $this->assertEquals('Ada', $dataset['customer_person']['person_parent']['person_lastname']);
    $this->assertEquals(null, $dataset['customer_person']['person_parent']['person_birthdate']);

    $this->assertNotNull($dataset['customer_id']);
    $this->assertNotNull($dataset['customer_person']['person_id']);
    $this->assertNotNull($dataset['customer_contactentries'][0]['contactentry_id']);

    $this->assertEquals($dataset['customer_person_id'], $dataset['customer_person']['person_id']);
    $this->assertEquals($dataset['customer_contactentries'][0]['contactentry_customer_id'], $dataset['customer_id']);

    //
    // Cleanup
    //
    $customerModel->saveWithChildren([
      $customerModel->getPrimarykey() => $customerId,
      // Implicitly remove contactentries by saving an empty collection (Not null!)
      'customer_contactentries' => []
    ]);
    $customerModel->delete($customerId);
    $personModel->delete($personId);
    $parentPersonModel->delete($parentPersonId);
  }

  /**
   * [testVirtualFieldResultCollectionHandling description]
   */
  public function testVirtualFieldResultCollectionHandling(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addCollectionModel($contactentryModel = $this->getModel('contactentry'));

    $dataset = [
      'customer_no' => 'K1002',
      'customer_contactentries' => [
        [ 'contactentry_name' => 'Entry1', 'contactentry_telephone' => '+49123123123' ],
        [ 'contactentry_name' => 'Entry2', 'contactentry_telephone' => '+49234234234' ],
        [ 'contactentry_name' => 'Entry3', 'contactentry_telephone' => '+49345345345' ],
      ]
    ];

    $customerModel->saveWithChildren($dataset);
    $id = $customerModel->lastInsertId();

    $customer = $customerModel->load($id);
    $this->assertCount(3, $customer['customer_contactentries']);

    // delete the middle contactentry
    unset($customer['customer_contactentries'][1]);

    // store PKEYs of other entries
    $contactentryIds = array_column($customer['customer_contactentries'], 'contactentry_id');
    $customerModel->saveWithChildren($customer);

    $customerModified = $customerModel->load($id);
    $this->assertCount(2, $customerModified['customer_contactentries']);

    $contactentryIdsVerify = array_column($customerModified['customer_contactentries'], 'contactentry_id');

    // assert the IDs haven't changed
    $this->assertEquals($contactentryIds, $contactentryIdsVerify);

    // assert nothing happens if a null value is provided or being unset
    $customerUnsetCollection = $customerModified;
    unset($customerUnsetCollection['customer_contactentries']);
    $customerModel->saveWithChildren($customerUnsetCollection);
    $this->assertEquals($customerModified['customer_contactentries'], $customerModel->load($id)['customer_contactentries']);

    $customerNullCollection = $customerModified;
    $customerNullCollection['customer_contactentries'] = null;
    $customerModel->saveWithChildren($customerNullCollection);
    $this->assertEquals($customerModified['customer_contactentries'], $customerModel->load($id)['customer_contactentries']);

    //
    // Cleanup
    //
    $customerModel->saveWithChildren([
      $customerModel->getPrimarykey() => $id,
      // Implicitly remove contactentries by saving an empty collection (Not null!)
      'customer_contactentries' => []
    ]);
    $customerModel->delete($id);
  }


  /**
   * Tests trying ::addCollectionModel w/o having the respective config.
   */
  public function testAddCollectionModelMissingCollectionConfig(): void {
    // Testdata model does not have a collection config
    // (or, at least, it shouldn't have)
    $model = $this->getModel('testdata');
    $this->assertFalse($model->getConfig()->exists('collection'));

    $this->expectExceptionMessage('EXCEPTION_NO_COLLECTION_KEY');
    $model->addCollectionModel($this->getModel('details'));
  }

  /**
   * Tests trying to ::addCollectionModel with an unsupported/unspecified model
   */
  public function testAddCollectionModelIncompatible(): void {
    $model = $this->getModel('customer');
    $this->expectExceptionMessage('EXCEPTION_UNKNOWN_COLLECTION_MODEL');
    $model->addCollectionModel($this->getModel('person'));
  }

  /**
   * Tests trying to ::addCollectionModel with a valid collection model
   * but simply a wrong or nonexisting field
   */
  public function testAddCollectionModelInvalidModelField(): void {
    $model = $this->getModel('customer');
    $this->expectExceptionMessage('EXCEPTION_NO_COLLECTION_CONFIG');
    $model->addCollectionModel(
      $this->getModel('contactentry'), // Compatible
      'nonexisting_collection_field'   // different field - or incompatible
    );
  }

  /**
   *  Tests trying to ::addCollectionModel with an incompatible model
   *  but a valid/existing collection field key
   */
  public function testAddCollectionModelValidModelFieldIncompatibleModel(): void {
    $model = $this->getModel('customer');
    $this->expectExceptionMessage('EXCEPTION_MODEL_ADDCOLLECTIONMODEL_INCOMPATIBLE');
    $model->addCollectionModel(
      $this->getModel('person'), // Incompatible
      'customer_contactentries'  // Existing/valid field, but irrelevant for the model to be joined
    );
  }

  /**
   * Tests various cases of collection retrieval
   */
  public function testGetNestedCollections(): void {
    // Model w/o any collection config
    $this->assertEmpty($this->getModel('testdata')->getNestedCollections());

    // Model with available, but unused collection
    $this->assertEmpty(
      $this->getModel('customer')
        ->getNestedCollections()
    );

    // Model with available and _used_ collection
    $collections = $this->getModel('customer')
      ->addCollectionModel($this->getModel('contactentry'))
      ->getNestedCollections();

    $this->assertNotEmpty($collections);
    $this->assertCount(1, $collections);

    $collectionPlugin = $collections['customer_contactentries'];
    $this->assertInstanceOf(\codename\core\model\plugin\collection::class, $collectionPlugin);

    $this->assertEquals('customer', $collectionPlugin->baseModel->getIdentifier());
    $this->assertEquals('customer_id', $collectionPlugin->getBaseField());
    $this->assertEquals('customer_contactentries', $collectionPlugin->field->get());
    $this->assertEquals('contactentry', $collectionPlugin->collectionModel->getIdentifier());
    $this->assertEquals('contactentry_customer_id', $collectionPlugin->getCollectionModelBaseRefField());
  }

  /**
   * test saving (expect a crash) when having two models joined ambiguously
   * in virtual field result mode
   */
  public function testVirtualFieldResultSavingFailedAmbiguousJoins(): void {
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel($personModel = $this->getModel('person'))
      ->addModel($personModel = $this->getModel('person')) // double joined
      ->addCollectionModel($contactentryModel = $this->getModel('contactentry'));

    $dataset = [
      'customer_no' => 'K1001',
      'customer_person' => [
        'person_firstname'  => 'John',
        'person_lastname'   => 'Doe',
        'person_birthdate'  => '1970-01-01',
      ],
      'customer_contactentries' => [
        [ 'contactentry_name' => 'Phone', 'contactentry_telephone' => '+49123123123' ]
      ]
    ];

    $this->assertTrue($customerModel->isValid($dataset));

    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_CHILDREN_AMBIGUOUS_JOINS');

    $customerModel->saveWithChildren($dataset);

    // No need to cleanup, as it must fail beforehand
  }

  /**
   * tests a runtime-based virtual field
   */
  public function testVirtualFieldQuery(): void {
    $model = $this->getModel('testdata')->setVirtualFieldResult(true);
    $model->addVirtualField('virtual_field', function($dataset) {
      return $dataset['testdata_id'];
    });
    $res = $model->search()->getResult();

    $this->assertCount(4, $res);
    foreach($res as $r) {
      $this->assertEquals($r['testdata_id'], $r['virtual_field']);
    }
  }

  /**
   * [testForcedVirtualJoinWithVirtualFieldResult description]
   */
  public function testForcedVirtualJoinWithVirtualFieldResult(): void {
    $this->testForcedVirtualJoin(true);
  }

  /**
   * [testForcedVirtualJoinWithoutVirtualFieldResult description]
   */
  public function testForcedVirtualJoinWithoutVirtualFieldResult(): void {
    $this->testForcedVirtualJoin(false);
  }

  /**
   * [testForcedVirtualJoin description]
   * @param bool $virtualFieldResult [description]
   */
  protected function testForcedVirtualJoin(bool $virtualFieldResult): void {
    //
    // Store test data
    //
    $saveCustomerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel($savePersonModel = $this->getModel('person')->setVirtualFieldResult(true));
    $saveCustomerModel->saveWithChildren([
      'customer_no' => 'fvj',
      'customer_person' => [
        'person_firstname' => 'forced',
        'person_lastname' => 'virtualjoin',
      ]
    ]);
    $customerId = $saveCustomerModel->lastInsertId();
    $personId = $savePersonModel->lastInsertId();

    $referenceCustomerModel = $this->getModel('customer')->setVirtualFieldResult($virtualFieldResult)
      ->addModel($referencePersonModel = $this->getModel('person')->setVirtualFieldResult($virtualFieldResult));

    $referenceDataset = $referenceCustomerModel->load($customerId);

    //
    // new model that is forced to do a virtual join
    //

    // NOTE/IMPORTANT: force virtual join state has to be set *BEFORE* joining
    $personModel = $this->getModel('person');
    $personModel->setForceVirtualJoin(true);

    $customerModel = $this->getModel('customer')->setVirtualFieldResult($virtualFieldResult)
      ->addModel($personModel->setVirtualFieldResult($virtualFieldResult));

    $customerModel->saveLastQuery = true;
    $personModel->saveLastQuery = true;

    $compareDataset = $customerModel->load($customerId);

    $customerLastQuery = $customerModel->getLastQuery();
    $personLastQuery = $personModel->getLastQuery();

    // assert that *BOTH* queries have been executed (not empty)
    $this->assertNotNull($customerLastQuery);
    $this->assertNotNull($personLastQuery);
    $this->assertNotEquals($customerLastQuery, $personLastQuery);

    // echo(chr(10)."---REFERENCE---".chr(10));
    // print_r($referenceDataset);
    // echo(chr(10)."---COMPARE---".chr(10));
    // print_r($compareDataset);

    foreach($referenceDataset as $key => $value) {
      if(is_array($value)) {
        foreach($value as $k => $v) {
          if($v !== null) {
            $this->assertEquals($v, $compareDataset[$key][$k]);
          }
        }
      } else {
        if($value !== null) {
          $this->assertEquals($value, $compareDataset[$key]);
        }
      }
    }

    // Assert both datasets are equal
    // $this->assertEquals($referenceDataset, $compareDataset);
    // NOTE: doesn't work right now, because:
    // $this->addWarning('Some bug when doing forced virtual joins and unjoined vfields exist');
    // NOTE/CHANGED 2021-04-13: fixed.

    // make sure to clean up
    $saveCustomerModel->delete($customerId);
    $savePersonModel->delete($personId);
    $this->assertEmpty($saveCustomerModel->load($customerId));
    $this->assertEmpty($savePersonModel->load($personId));
  }

  /**
   * [testModelJoinWithJson description]
   */
  public function testModelJoinWithJson(): void {
    // inject some base data, first
    $model = $this->getModel('person')
      ->addModel($this->getModel('country'));

    $model->save([
      'person_firstname'  => 'German',
      'person_lastname'   => 'Resident',
      'person_country'    => 'DE',
    ]);
    $id = $model->lastInsertId();

    $res = $model->load($id);
    $this->assertEquals('DE', $res['person_country']);
    $this->assertEquals('DE', $res['country_code']);
    $this->assertEquals('Germany', $res['country_name']);

    $model->delete($id);
    $this->assertEmpty($model->load($id));

    //
    // save another one, but without FKEY value for country
    //
    $model->save([
      'person_firstname'  => 'Resident',
      'person_lastname'   => 'Without Country',
      'person_country'    => null,
    ]);
    $id = $model->lastInsertId();

    $res = $model->load($id);
    $this->assertEquals(null, $res['person_country']);
    $this->assertEquals(null, $res['country_code']);
    $this->assertEquals(null, $res['country_name']);

    $model->delete($id);
    $this->assertEmpty($model->load($id));
  }

  /**
   * [testInvalidFilterOperator description]
   */
  public function testInvalidFilterOperator(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_INVALID_OPERATOR');
    $model = $this->getModel('testdata');
    $model->addFilter('testdata_integer', 42, '%&/');
  }

  /**
   * [testLikeFilters description]
   */
  public function testLikeFilters(): void {
    $model = $this->getModel('testdata');

    // NOTE: this is case sensitive on PG
    $res = $model
      ->addFilter('testdata_text', 'F%', 'LIKE')
      ->search()->getResult();
    $this->assertCount(2, $res);

    // NOTE: this is case sensitive on PG
    $res = $model
      ->addFilter('testdata_text', 'f%', 'ILIKE')
      ->search()->getResult();
    $this->assertCount(2, $res);
  }

  /**
   * [testSuccessfulCreateAndDeleteTransaction description]
   */
  public function testSuccessfulCreateAndDeleteTransaction(): void {
    $testTransactionModel = $this->getModel('testdata');

    $transaction = new \codename\core\transaction('test', [ $testTransactionModel ]);
    $transaction->start();

    // insert a new entry
    $testTransactionModel->save([
      'testdata_integer'  => 999,
    ]);
    $id = $testTransactionModel->lastInsertId();

    // load the new dataset in the transaction
    $newDataset = $testTransactionModel->load($id);
    $this->assertEquals(999, $newDataset['testdata_integer']);

    // delete it
    $testTransactionModel->delete($id);

    // end transaction, as if nothing happened
    $transaction->end();

    // Make sure it hasn't changed
    $this->assertEquals(4, $testTransactionModel->getCount());
  }

  /**
   * [testOrderLimitOffset description]
   */
  public function testNestedOrder(): void {
    // Generic model features
    // Offset [& Limit & Order]
    $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
      ->addModel($personModel = $this->getModel('person')->setVirtualFieldResult(true));

    $customerIds = [];
    $personIds = [];

    $datasets = [
      [
        'customer_no' => 'A1000',
        'customer_person' => [
          'person_firstname' => 'Alex',
          'person_lastname'  => 'Anderson',
          'person_birthdate' => '1978-02-03',
        ],
      ],
      [
        'customer_no' => 'A1001',
        'customer_person' => [
          'person_firstname' => 'Bridget',
          'person_lastname'  => 'Balmer',
          'person_birthdate' => '1981-11-15',
        ],
      ],
      [
        'customer_no' => 'A1002',
        'customer_person' => [
          'person_firstname' => 'Christian',
          'person_lastname'  => 'Crossback',
          'person_birthdate' => '1990-04-19',
        ],
      ],
      [
        'customer_no' => 'A1003',
        'customer_person' => [
          'person_firstname' => 'Dodgy',
          'person_lastname'  => 'Data',
          'person_birthdate' => null,
        ],
      ]
    ];

    foreach($datasets as $d) {
      $customerModel->saveWithChildren($d);
      $customerIds[] = $customerModel->lastInsertId();
      $personIds[] = $personModel->lastInsertId();
    }

    $customerModel->addOrder('person.person_birthdate', 'DESC');
    $res = $customerModel->search()->getResult();

    $this->assertEquals([ 'A1002', 'A1001', 'A1000', 'A1003' ], array_column($res, 'customer_no'));
    $this->assertEquals([ 'Christian', 'Bridget', 'Alex', 'Dodgy' ], array_map(function($dataset) {
      return $dataset['customer_person']['person_firstname'];
    }, $res));

    // cleanup
    foreach($customerIds as $id) {
      $customerModel->delete($id);
    }
    foreach($personIds as $id) {
      $personModel->delete($id);
    }
  }


  /**
   * [testOrderLimitOffset description]
   */
  public function testOrderLimitOffset(): void {
    // Generic model features
    // Offset [& Limit & Order]
    $testLimitModel = $this->getModel('testdata');
    $testLimitModel->addOrder('testdata_id', 'ASC');
    $testLimitModel->setLimit(1);
    $testLimitModel->setOffset(1);
    $res = $testLimitModel->search()->getResult();
    $this->assertCount(1, $res);
    $this->assertEquals('bar', $res[0]['testdata_text']);
    $this->assertEquals(4.25, $res[0]['testdata_number']);
  }

  /**
   * Tests setting limit & offset twice (reset)
   * as only ONE limit and offset is allowed at a time
   */
  public function testLimitOffsetReset(): void {
    $model = $this->getModel('testdata');
    $model->addOrder('testdata_id', 'ASC');
    $model->setLimit(1);
    $model->setOffset(1);
    $model->setLimit(0);
    $model->setOffset(0);
    $res = $model->search()->getResult();
    $this->assertCount(4, $res);
  }

  /**
   * Tests whether calling model::addOrder() using a nonexisting field
   * throws an exception
   */
  public function testAddOrderOnNonexistingFieldWillThrow(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model::EXCEPTION_ADDORDER_FIELDNOTFOUND);
    $model = $this->getModel('testdata');
    $model->addOrder('testdata_nonexisting', 'ASC');
  }

  /**
   * Tests updating a structure field (simple)
   */
  public function testStructureData(): void {
    $model = $this->getModel('testdata');
    $res = $model
      ->addFilter('testdata_text', 'foo')
      ->addFilter('testdata_date', '2021-03-22')
      ->addFilter('testdata_number', 3.14)
      ->search()->getResult();
    $this->assertCount(1, $res);

    $testdata = $res[0];
    $id = $testdata[$model->getPrimarykey()];

    $model->save([
      $model->getPrimarykey() => $testdata[$model->getPrimarykey()],
      'testdata_structure'    => [ 'changed' => true ],
    ]);
    $updated = $model->load($id);
    $this->assertEquals([ 'changed' => true ], $updated['testdata_structure']);
    $model->save($testdata);
    $restored = $model->load($id);
    $this->assertEquals($testdata['testdata_structure'], $restored['testdata_structure']);
  }

  /**
   * tests model::getCount() when having a grouped query
   * should return the final count of results
   */
  public function testGroupedGetCount(): void {
    $model = $this->getModel('testdata');
    $model->addGroup('testdata_text');
    $this->assertEquals(2, $model->getCount());
  }

  /**
   * Tests correct aliasing when using the same model twice
   * and calling ->getCount()
   */
  public function testGetCountAliasing(): void {
    $model = $this->getModel('person')
      ->addModel($this->getModel('person'));

    $this->assertEquals(0, $model->getCount());
  }

  /**
   * Tests grouping on a calculated field
   */
  public function testAddGroupOnCalculatedFieldDoesNotCrash(): void {
    $model = $this->getModel('testdata');
    // For the sake of simplicity: just do a simple alias here...
    $model->addCalculatedField('calc_field', '(testdata_text)');
    $model->addGroup('calc_field');

    // We do not check for data integrity in this test.
    $this->expectNotToPerformAssertions();
    $model->search()->getResult();
  }

  /**
   * Tests grouping on a nested model's calculated field
   * which in which case the alias of the model MUST NOT propagate
   * as it is a unique, temporary field
   */
  public function testAddGroupOnNestedCalculatedFieldDoesNotCrash(): void {
    $model = $this->getModel('testdata')
      ->addModel($detailsModel = $this->getModel('details'));

    // For the sake of simplicity: just do a simple alias here...
    $detailsModel->addCalculatedField('nested_calc_field', '(details_data)');
    $detailsModel->addGroup('nested_calc_field');

    // We do not check for data integrity in this test.
    $this->expectNotToPerformAssertions();
    $model->search()->getResult();
  }

  /**
   * Tests whether we get an exception when trying to group
   * on a nonexisting field
   */
  public function testAddGroupNonExistingField(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\model::EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST);

    $model = $this->getModel('testdata')
      ->addModel($detailsModel = $this->getModel('details'));

    $model->addGroup('nonexisting');
  }

  /**
   * [testAmbiguousAliasFieldsNormalization description]
   */
  public function testAmbiguousAliasFieldsNormalization(): void {
    $model = $this->getModel('testdata')
      ->addField('testdata_text', 'aliasedField')
      ->addModel(
        $detailsModel = $this->getModel('details')
          ->addField('details_data', 'aliasedField')
      );

    $res = $model->search()->getResult();

    // Same-level keys mapped to array
    $this->assertEquals([
      [ 'foo', null ],
      [ 'bar', null ],
      [ 'foo', null ],
      [ 'bar', null ],
    ], array_column($res, 'aliasedField'));

    // Modify model to put details into a virtual field
    $model->setVirtualFieldResult(true);
    $model->getNestedJoins('details')[0]->virtualField = 'temp_virtual';

    $res2 = $model->search()->getResult();

    $this->assertEquals([ 'foo', 'bar', 'foo', 'bar' ], array_column($res2, 'aliasedField'));
    $this->assertEquals([ null, null, null, null ], array_column(array_column($res2, 'temp_virtual'), 'aliasedField'));
  }

  /**
   * [testAggregateCount description]
   */
  public function testAggregateCount(): void {
    //
    // Aggregate: count plugin
    //
    $testCountModel = $this->getModel('testdata');
    $testCountModel->addAggregateField('entries_count', 'count', 'testdata_id');

    // count w/o filters
    $this->assertEquals(4, $testCountModel->search()->getResult()[0]['entries_count']);

    // w/ simple filter added
    $testCountModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals(3, $testCountModel->search()->getResult()[0]['entries_count']);
  }

  /**
   * [testAggregateCountDistinct description]
   */
  public function testAggregateCountDistinct(): void {
    //
    // Aggregate: count_distinct plugin
    //
    $testCountDistinctModel = $this->getModel('testdata');
    $testCountDistinctModel->addAggregateField('entries_count', 'count_distinct', 'testdata_text');

    // count w/o filters
    $this->assertEquals(2, $testCountDistinctModel->search()->getResult()[0]['entries_count']);

    // w/ simple filter added - we only expect a count of 1
    $testCountDistinctModel
      ->addFilter('testdata_datetime', '2021-03-23', '>=');
    $this->assertEquals(1, $testCountDistinctModel->search()->getResult()[0]['entries_count']);
  }

  /**
   * [testAggregateSum description]
   */
  public function testAggregateSum(): void {
    //
    // Aggregate: sum plugin
    //
    $testSumModel = $this->getModel('testdata');
    $testSumModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');

    // count w/o filters
    $this->assertEquals(48, $testSumModel->search()->getResult()[0]['entries_sum']);

    // w/ simple filter added
    $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals(6, $testSumModel->search()->getResult()[0]['entries_sum']);

    // no entries matching filter
    $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
    $this->assertEquals(0, $testSumModel->search()->getResult()[0]['entries_sum']);
  }

  /**
   * [testAggregateAvg description]
   */
  public function testAggregateAvg(): void {
    //
    // Aggregate: avg plugin
    //
    $testSumModel = $this->getModel('testdata');
    $testSumModel->addAggregateField('entries_avg', 'avg', 'testdata_number');

    // count w/o filters
    $this->assertEquals((3.14 + 4.25 + 5.36 + 0.99)/4, $testSumModel->search()->getResult()[0]['entries_avg']);

    // w/ simple filter added
    $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals((3.14 + 4.25 + 5.36)/3, $testSumModel->search()->getResult()[0]['entries_avg']);

    // no entries matching filter
    $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
    $this->assertEquals(0, $testSumModel->search()->getResult()[0]['entries_avg']);
  }

  /**
   * [testAggregateDatetimeYear description]
   */
  public function testAggregateDatetimeYear(): void {
    //
    // Aggregate DateTime plugin
    //
    $testYearModel = $this->getModel('testdata');
    $testYearModel->addAggregateField('entries_year1', 'year', 'testdata_datetime');
    $testYearModel->addAggregateField('entries_year2', 'year', 'testdata_date');
    $testYearModel->addOrder('testdata_id', 'ASC');
    $yearRes = $testYearModel->search()->getResult();
    $this->assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year1'));
    $this->assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year2'));
  }

  /**
   * [testAggregateGroupedSumOrderByAggregateField description]
   */
  public function testAggregateGroupedSumOrderByAggregateField(): void {
    $testYearModel = $this->getModel('testdata');
    $testYearModel->addAggregateField('entries_year1', 'year', 'testdata_datetime');
    $testYearModel->addAggregateField('entries_year2', 'year', 'testdata_date');
    // add a grouping modifier (WARNING, instance modified)
    // introduce additional summing
    // and order by calculated/aggregate field
    $testYearModel->addGroup('entries_year1');
    $testYearModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');
    $testYearModel->addOrder('entries_year1', 'ASC');
    $yearGroupedRes = $testYearModel->search()->getResult();

    $this->assertEquals(2019, $yearGroupedRes[0]['entries_year1']);
    $this->assertEquals(42,   $yearGroupedRes[0]['entries_sum']);
    $this->assertEquals(2021, $yearGroupedRes[1]['entries_year1']);
    $this->assertEquals(6,    $yearGroupedRes[1]['entries_sum']);
  }

  /**
   * [testAggregateDatetimeInvalid description]
   */
  public function testAggregateDatetimeInvalid(): void {
    //
    // Tests an invalid type config for Aggregate DateTime plugin
    //
    $this->expectException(\codename\core\exception::class);
    $model = $this->getModel('testdata');
    $model->addAggregateField('entries_invalid1', 'invalid', 'testdata_datetime');
    $res = $model->search()->getResult();
  }

  /**
   * [testAggregateDatetimeQuarter description]
   */
  public function testAggregateDatetimeQuarter(): void {
    //
    // Aggregate Quarter plugin
    //
    $testQuarterModel = $this->getModel('testdata');
    $testQuarterModel->addAggregateField('entries_quarter1', 'quarter', 'testdata_datetime');
    $testQuarterModel->addAggregateField('entries_quarter2', 'quarter', 'testdata_date');
    $testQuarterModel->addOrder('testdata_id', 'ASC');
    $res = $testQuarterModel->search()->getResult();
    $this->assertEquals([1, 1, 1, 1], array_column($res, 'entries_quarter1'));
    $this->assertEquals([1, 1, 1, 1], array_column($res, 'entries_quarter2'));
  }

  /**
   * [testAggregateDatetimeMonth description]
   */
  public function testAggregateDatetimeMonth(): void {
    //
    // Aggregate DateTime plugin
    //
    $testMonthModel = $this->getModel('testdata');
    $testMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
    $testMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
    $testMonthModel->addOrder('testdata_id', 'ASC');
    $res = $testMonthModel->search()->getResult();
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));
  }

  /**
   * [testAggregateDatetimeDay description]
   */
  public function testAggregateDatetimeDay(): void {
    //
    // Aggregate DateTime plugin
    //
    $model = $this->getModel('testdata');
    $model->addAggregateField('entries_day1', 'day', 'testdata_datetime');
    $model->addAggregateField('entries_day2', 'day', 'testdata_date');
    $model->addOrder('testdata_id', 'ASC');
    $res = $model->search()->getResult();
    $this->assertEquals([22, 22, 23, 01], array_column($res, 'entries_day1'));
    $this->assertEquals([22, 22, 23, 01], array_column($res, 'entries_day2'));
  }

  /**
   * [testAggregateFilterSimple description]
   */
  public function testAggregateFilterSimple(): void {
    // Aggregate Filter
    $testAggregateFilterMonthModel = $this->getModel('testdata');

    $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
    $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month1', 3, '>=');
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month2', 3, '>=');

    // WARNING: sqlite doesn't support HAVING without GROUP BY
    $testAggregateFilterMonthModel->addGroup('testdata_id');

    $res = $testAggregateFilterMonthModel->search()->getResult();
    $this->assertEquals([3, 3, 3], array_column($res, 'entries_month1'));
    $this->assertEquals([3, 3, 3], array_column($res, 'entries_month2'));
  }

  /**
   * [testAggregateFilterValueArray description]
   */
  public function testAggregateFilterValueArray(): void {
    // Aggregate Filter
    $testAggregateFilterMonthModel = $this->getModel('testdata');

    $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
    $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month1', [1, 3]);
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month2', [1, 3]);

    // WARNING: sqlite doesn't support HAVING without GROUP BY
    $testAggregateFilterMonthModel->addGroup('testdata_id');

    $res = $testAggregateFilterMonthModel->search()->getResult();
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));
  }

  /**
   * [testAggregateFilterValueArraySimple description]
   */
  public function testAggregateFilterValueArraySimple(): void {
    // Aggregate Filter
    $model = $this->getModel('testdata');

    // Actually, there's no real aggregate field for this test
    // Instead, we just alias existing fields.
    $model->addField('testdata_boolean', 'boolean_aliased');
    $model->addField('testdata_integer', 'integer_aliased');
    $model->addField('testdata_number', 'number_aliased');

    // WARNING: sqlite doesn't support HAVING without GROUP BY
    $model->addGroup('testdata_id');

    $model->saveLastQuery = true;

    $this->assertEquals([3.14, 4.25, 5.36, 0.99], array_column($model->search()->getResult(), 'testdata_number'));

    //
    // compacted serial tests
    //
    $filterTests = [
      //
      // Datatype estimation for booleans
      //
      [
        'field'     => 'boolean_aliased',
        'value'     => [ true ],
        'expected'  => [ 3.14, 4.25 ]
      ],
      [
        'field'     => 'boolean_aliased',
        'value'     => [ true, false ],
        'expected'  => [ 3.14, 4.25, 5.36, 0.99 ]
      ],
      [
        'field'     => 'boolean_aliased',
        'value'     => [ false ],
        'expected'  => [ 5.36, 0.99 ]
      ],

      //
      // Datatype estimation for integers
      //
      [
        'field'     => 'integer_aliased',
        'value'     => [ 1 ],
        'expected'  => [ 5.36 ]
      ],
      [
        'field'     => 'integer_aliased',
        'value'     => [ 1, 2, 3, 42 ],
        'expected'  => [ 3.14, 4.25, 5.36, 0.99 ]
      ],
      [
        'field'     => 'integer_aliased',
        'value'     => [ 3, 42 ],
        'expected'  => [ 3.14, 0.99 ]
      ],

      //
      // Datatype estimation for numbers (floats, doubles, decimals)
      //
      [
        'field'     => 'number_aliased',
        'value'     => [ 5.36 ],
        'expected'  => [ 5.36 ]
      ],
      [
        'field'     => 'number_aliased',
        'value'     => [ 3.14, 4.25, 5.36, 0.99 ],
        'expected'  => [ 3.14, 4.25, 5.36, 0.99 ]
      ],
      [
        'field'     => 'number_aliased',
        'value'     => [ 3.14, 0.99 ],
        'expected'  => [ 3.14, 0.99 ]
      ],
    ];

    foreach($filterTests as $i => $f) {
      // use aggregate filter
      $model->addAggregateFilter($f['field'], $f['value']);
      $this->assertEquals($f['expected'], array_column($model->search()->getResult(), 'testdata_number'));

      // the same, but using FCs - NOTE: does not exist yet (model::aggregateFiltercollection)
      // this only works for SQLite due to its nature.
      // $model->addFilterCollection([[ 'field' => $f['field'], 'operator' => '=', 'value' => $f['value'] ]]);
      // $this->assertEquals($f['expected'], array_column($model->search()->getResult(), 'testdata_number'));
    }
  }

  /**
   * [testFieldAlias description]
   */
  public function testFieldAliasWithFilter(): void  {
    $this->markTestIncomplete('Aliased filter implementation on differing platforms is still unclear');
    $model = $this->getModel('testdata');

    //
    // NOTE/WARNING:
    // - on MySQL you can do a HAVING clause without GROUP BY, but not filter for an aliased column in WHERE
    // - on SQLite you CANNOT have a HAVING clause without GROUP BY, but you can filter for an aliased column in WHERE
    //
    $res = $model
      ->hideAllFields()
      ->addField('testdata_text', 'aliased_text')
      ->addFilter('testdata_integer', 3)
      // ->addFilter('aliased_text', 'foo')
      ->addAggregateFilter('aliased_text', 'foo')
      ->search()->getResult();

    $this->assertCount(1, $res);
    $this->assertEquals([ 'aliased_text' => 'foo'], $res[0]);
  }

  /**
   * Tests ->addFilter() with an empty array value as to-be-filtered-for value
   * This is an edge case which might change in the future.
   * CHANGED 2021-09-13: we now trigger a E_USER_NOTICE when an empty array ([]) is provided as filter value
   */
  public function testAddFilterWithEmptyArrayValue(): void {
    $model = $this->getModel('testdata');

    // NOTE: we have to override the error handler for a short period of time
    set_error_handler(null, E_USER_NOTICE);

    //
    // WARNING: to avoid any issue with error handlers
    // we try to keep the amount of calls not covered by the generic handler
    // at a minimum
    //
    try {
      @$model->addFilter('testdata_text', []); // this is discarded internally/has no effect
    } catch (\Throwable $t) {}

    restore_error_handler();

    $this->assertEquals(error_get_last()['message'], 'Empty array filter values have no effect on resultset');
    $this->assertEquals(4, $model->getCount());
  }

  /**
   * see above
   * CHANGED 2021-09-13: we now trigger a E_USER_NOTICE when an empty array ([]) is provided as filter value
   */
  public function testAddFiltercollectionWithEmptyArrayValue(): void {
    $model = $this->getModel('testdata');

    // NOTE: we have to override the error handler for a short period of time
    set_error_handler(null, E_USER_NOTICE);

    //
    // WARNING: to avoid any issue with error handlers
    // we try to keep the amount of calls not covered by the generic handler
    // at a minimum
    //
    try {
      @$model->addFiltercollection([
        [ 'field' => 'testdata_text', 'operator' => '=', 'value' => [] ]
      ]); // this is discarded internally/has no effect
    } catch (\Throwable $t) {}

    restore_error_handler();

    $this->assertEquals(error_get_last()['message'], 'Empty array filter values have no effect on resultset');
    $this->assertEquals(4, $model->getCount());
  }

  /**
   * see above
   * CHANGED 2021-09-13: we now trigger a E_USER_NOTICE when an empty array ([]) is provided as filter value
   */
  public function testAddDefaultfilterWithEmptyArrayValue(): void {
    $model = $this->getModel('testdata');

    // NOTE: we have to override the error handler for a short period of time
    set_error_handler(null, E_USER_NOTICE);

    //
    // WARNING: to avoid any issue with error handlers
    // we try to keep the amount of calls not covered by the generic handler
    // at a minimum
    //
    try {
      @$model->addDefaultfilter('testdata_text', []); // this is discarded internally/has no effect
    } catch (\Throwable $t) {}

    restore_error_handler();

    $this->assertEquals(error_get_last()['message'], 'Empty array filter values have no effect on resultset');
    $this->assertEquals(4, $model->getCount());
  }

  /**
   * see above
   * CHANGED 2021-09-13: we now trigger a E_USER_NOTICE when an empty array ([]) is provided as filter value
   */
  public function testAddDefaultFiltercollectionWithEmptyArrayValue(): void {
    $model = $this->getModel('testdata');

    // NOTE: we have to override the error handler for a short period of time
    set_error_handler(null, E_USER_NOTICE);

    //
    // WARNING: to avoid any issue with error handlers
    // we try to keep the amount of calls not covered by the generic handler
    // at a minimum
    //
    try {
      @$model->addDefaultFilterCollection([
        [ 'field' => 'testdata_text', 'operator' => '=', 'value' => [] ]
      ]); // this is discarded internally/has no effect
    } catch (\Throwable $t) {}

    restore_error_handler();

    $this->assertEquals(error_get_last()['message'], 'Empty array filter values have no effect on resultset');
    $this->assertEquals(4, $model->getCount());
  }

  /**
   * [testAddDefaultfilterWithArrayValue description]
   */
  public function testAddDefaultfilterWithArrayValue(): void {
    $model = $this->getModel('testdata');
    $model->addDefaultfilter('testdata_date', [ '2021-03-22', '2021-03-23' ]);
    $this->assertCount(3, $model->search()->getResult());

    // second call, filter should still be active
    $this->assertCount(3, $model->search()->getResult());

    // third call, filter should still be active
    // we reset explicitly
    $model->reset();
    $this->assertCount(3, $model->search()->getResult());
  }

  /**
   * [testDefaultfilterSimple description]
   */
  public function testDefaultfilterSimple(): void {
    $model = $this->getModel('testdata');

    // generic default filter
    $model->addDefaultfilter('testdata_number', 3.5, '>');

    $res1 = $model->search()->getResult();
    $res2 = $model->search()->getResult();
    $this->assertCount(2, $res1);
    $this->assertEquals($res1, $res2);

    // add a filter on the fly - and we expect
    // an empty resultset
    $res = $model
      ->addFilter('testdata_text', 'nonexisting')
      ->search()->getResult();
    $this->assertCount(0, $res);

    // try to reduce the resultset to 1
    // in conjunction with the above default filter
    $res = $model
      ->addFilter('testdata_integer', 1, '<=')
      ->search()->getResult();
    $this->assertCount(1, $res);
  }

  /**
   * Tests using a discrete model as root
   * and compares equality.
   */
  public function testAdhocDiscreteModelAsRoot(): void {
    $testdataModel = $this->getModel('testdata');
    $originalRes = $testdataModel->search()->getResult();
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $testdataModel);
    $discreteRes = $discreteModelTest->search()->getResult();
    $this->assertEquals($originalRes, $discreteRes);

    // TODO: add some filters and compare again.
  }

  /**
   * Fun with discrete models
   */
  public function testAdhocDiscreteModelComplex(): void {
    $testdataModel = $this->getModel('testdata');
    $testdataModel
      ->hideAllFields()
      ->addField('testdata_id', 'testdataidaliased')
      ->addCalculatedField('calculated', 'testdata_integer * 4')
      // ->addDefaultfilter('testdata_id', 2, '>')
      ->addGroup('testdata_date')
      ->addModel($this->getModel('details'))
      ;
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $testdataModel);
    $res = $discreteModelTest->search()->getResult();

    $this->assertCount(3, $res);

    $rootModel = $this->getModel('testdata')->setVirtualFieldResult(true)
      ->addCustomJoin(
        $discreteModelTest,
        \codename\core\model\plugin\join::TYPE_LEFT,
        'testdata_id',
        'testdataidaliased'
      );
    $rootModel->getNestedJoins('sample1')[0]->virtualField = 'virtualSample1';

    $res2 = $rootModel->search()->getResult();
    // print_r($res2);

    $this->assertCount(4, $res2);
    $this->assertEquals([ 12, null, 4, 168 ], array_column(array_column($res2, 'virtualSample1'), 'calculated'));

    $secondaryDiscreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample2', $testdataModel);
    $secondaryDiscreteModelTest->addCalculatedField('calcCeption', 'sample2.calculated * sample2.calculated');
    $rootModel->addCustomJoin(
        $secondaryDiscreteModelTest,
        \codename\core\model\plugin\join::TYPE_LEFT,
        'testdata_id',
        'testdataidaliased'
      );
    $rootModel->getNestedJoins('sample2')[0]->virtualField = 'virtualSample2';

    $rootModel->addCalculatedField('calcCeption2', 'sample1.calculated * sample2.calculated');

    $res3 = $rootModel->search()->getResult();
    // print_r($res3);

    $this->assertEquals([ 144, null, 16, 28224 ], array_column(array_column($res3, 'virtualSample2'), 'calcCeption'));
    $this->assertEquals([ 144, null, 16, 28224 ], array_column($res3, 'calcCeption2'));
  }

  /**
   * [testDiscreteModelLimit description]
   */
  public function testDiscreteModelLimitAndOffset(): void {
    $testdataModel = $this->getModel('testdata');
    $testdataModel
      ->hideAllFields()
      ->addField('testdata_id', 'testdataidaliased')
      ->addCalculatedField('calculated', 'testdata_integer * 4')
      // ->addDefaultfilter('testdata_id', 2, '>')
      ->addGroup('testdata_date')
      ->addModel($this->getModel('details'))
      ;

    // NOTE limit & offset instances get reset after query
    $testdataModel->setLimit(2)->setOffset(1);

    $originalRes = $testdataModel->search()->getResult();
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $testdataModel);

    // NOTE limit & offset instances get reset after query
    $testdataModel->setLimit(2)->setOffset(1);
    $discreteRes = $discreteModelTest->search()->getResult();

    $this->assertCount(2, $discreteRes);
    $this->assertEquals($originalRes, $discreteRes);
  }

  /**
   * [testDiscreteModelAddOrder description]
   */
  public function testDiscreteModelAddOrder(): void {
    //
    // NOTE ORDER BY in a subquery is ignored in MySQL for final output
    // See https://mariadb.com/kb/en/why-is-order-by-in-a-from-subquery-ignored/
    // But it is essential for LIMIT/OFFSETs used in the subquery!
    //
    $testdataModel = $this->getModel('testdata');

    // NOTE order instance gets reset after query
    $testdataModel->addOrder('testdata_id', 'DESC');
    $testdataModel->setOffset(2)->setLimit(2);

    $originalRes = $testdataModel->search()->getResult();
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $testdataModel);

    // NOTE order instance gets reset after query
    $testdataModel->addOrder('testdata_id', 'DESC');
    $testdataModel->setOffset(2)->setLimit(2);
    $discreteRes = $discreteModelTest->search()->getResult();

    $this->assertCount(2, $discreteRes);
    $this->assertEquals($originalRes, $discreteRes);

    // finally, query the thing with a zero offset
    // to make sure we have ORDER+LIMIT+OFFSET really working
    // inside the subquery
    // though the final order might be different.
    $testdataModel->addOrder('testdata_id', 'DESC');
    $testdataModel->setOffset(0)->setLimit(2);
    $offset0Res = $testdataModel->search()->getResult();

    $this->assertNotEquals($offset0Res, $originalRes);

    $this->assertLessThan(
      array_sum(array_column($offset0Res, 'testdata_id')), // Offset 0-based results should be topmost => sum of IDs must be greater
      array_sum(array_column($originalRes, 'testdata_id')) // ... and this sum must be LESS THAN the above.
    );
  }

  /**
   * [testDiscreteModelSimpleAggregate description]
   */
  public function testDiscreteModelSimpleAggregate(): void {
    $testdataModel = $this->getModel('testdata')
      ->addAggregateField('id_sum', 'sum', 'testdata_integer')
      ->addGroup('testdata_date')
      ->addDefaultAggregateFilter('id_sum', 10, '<=')
      ;

    $originalRes = $testdataModel->search()->getResult();
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $testdataModel);

    $discreteRes = $discreteModelTest->search()->getResult();

    $this->assertCount(2, $discreteRes);
    $this->assertEquals($originalRes, $discreteRes);
  }

  /**
   * [testDiscreteModelSaveWillThrow description]
   */
  public function testDiscreteModelSaveWillThrow(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented.');
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $this->getModel('testdata'));
    $discreteModelTest->save([ 'value' => 'doesnt matter']);
  }

  /**
   * [testDiscreteModelUpdateWillThrow description]
   */
  public function testDiscreteModelUpdateWillThrow(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented.');
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $this->getModel('testdata'));
    $discreteModelTest->update([ 'value' => 'doesnt matter']);
  }

  /**
   * [testDiscreteModelReplaceWillThrow description]
   */
  public function testDiscreteModelReplaceWillThrow(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented.');
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $this->getModel('testdata'));
    $discreteModelTest->replace([ 'value' => 'doesnt matter']);
  }

  /**
   * [testDiscreteModelDeleteWillThrow description]
   */
  public function testDiscreteModelDeleteWillThrow(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented.');
    $discreteModelTest = new \codename\core\model\schematic\discreteDynamic('sample1', $this->getModel('testdata'));
    $discreteModelTest->delete(1);
  }


  /**
   * Tests a case where the 'aliased' flag on a group plugin was always active
   * (and ignoring schema/table - on root, there's no currentAlias (null))
   * and causes severe errors when executing a query
   * (ambiguous column)
   */
  public function testGroupAliasBugFixed(): void {
    $model = $this->getModel('person')->setVirtualFieldResult(true)
      ->addModel($this->getModel('person'))
      ->addGroup('person_id');
    $res = $model->search()->getResult();
    $this->expectNotToPerformAssertions();
  }

  /**
   * [testNormalizeData description]
   * @return [type] [description]
   */
  public function testNormalizeData() {
    $originalDataset = [
      'testdata_datetime' => '2021-04-01 11:22:33',
      'testdata_text'     => 'normalizeTest',
      'testdata_date'     => '2021-01-01',
    ];

    $normalizeMe = $originalDataset;
    $normalizeMe['crapkey'] = 'crap';

    $model = $this->getModel('testdata');
    $normalized = $model->normalizeData($normalizeMe);
    $this->assertEquals($originalDataset, $normalized);
  }

  /**
   * [testValidate description]
   * @return [type] [description]
   */
  public function testValidateSimple() {
    $dataset = [
      'testdata_datetime' => '2021-13-01 11:22:33',
      'testdata_text'     => [ 'abc' => true ],
      'testdata_date'     => '0000-01-01',
    ];

    $model = $this->getModel('testdata');
    $this->assertFalse($model->isValid($dataset));

    $validationErrors = $model->validate($dataset)->getErrors();
    $this->assertCount(2, $validationErrors); // actually, we should have 3
  }

  /**
   * Test model::entry* wrapper functions
   * NOTE: they might interfere with regular queries
   */
  public function testEntryFunctions(): void {
    $entryModel = $this->getModel('testdata'); // model used for testing entry* functions
    $model = $this->getModel('testdata'); // model used for querying

    $dataset = [
      'testdata_datetime' => '2021-04-01 11:22:33',
      'testdata_text'     => 'entryMakeTest',
      'testdata_date'     => '2021-01-01',
      'testdata_number'   => 12345.6789,
      'testdata_integer'  => 222,
    ];

    $entryModel->entryMake($dataset);

    $entryModel->entryValidate(); // TODO: do something with the validation result?

    $entryModel->entrySave();
    $id = $entryModel->lastInsertId();
    $entryModel->reset();

    $model->hideAllFields()
      ->addField('testdata_datetime')
      ->addField('testdata_text')
      ->addField('testdata_date')
      ->addField('testdata_number')
      ->addField('testdata_integer')
      ;
    $queriedDataset = $model->load($id);
    $this->assertEquals($dataset, $queriedDataset);

    $entryModel->entryLoad($id);
    $entryModel->entryUpdate([
      'testdata_text' => 'updated',
    ]);
    $entryModel->entrySave();

    $modifiedDataset = $model->load($id);
    $this->assertEquals('updated', $modifiedDataset['testdata_text']);

    $entryModel->entryDelete();
    $this->assertEmpty($model->load($id));
  }

  /**
   * Basic Timemachine functionality
   */
  public function testTimemachineDelta(): void {
    $testdataTm = $this->getTimemachineEnabledModel('testdata');

    $res = $this->getModel('testdata')
      ->addFilter('testdata_text', 'foo')
      ->addFilter('testdata_date', '2021-03-22')
      ->addFilter('testdata_number', 3.14)
      ->search()->getResult();
    $this->assertCount(1, $res);
    $id = $res[0]['testdata_id'];

    $testdataTm->save([
      'testdata_id'       => $id,
      'testdata_integer'  => 888,
    ]);

    $timemachine = new \codename\core\timemachine($testdataTm);
    $history = $timemachine->getHistory($id);

    $delta = $timemachine->getDeltaData($id, 0);
    $this->assertEquals([ 'testdata_integer' => 3], $delta);

    $bigbangState = $timemachine->getHistoricData($id, 0);
    $this->assertEquals(3, $bigbangState['testdata_integer']);

    // restore via delta
    $testdataTm->save(array_merge([
      'testdata_id'       => $id,
    ], $delta));
  }

  /**
   * [getModel description]
   * @param  string $model [description]
   * @return \codename\core\model
   */
  protected static function getTimemachineEnabledModelStatic(string $model): \codename\core\model {
    $modelData = static::$models[$model];
    $instance = new timemachineEnabledSqlModel($modelData['schema'], $modelData['model'], $modelData['config']);

    $tmModelData = static::$models['timemachine'];
    $tmModel = new timemachineModel($tmModelData['schema'], $tmModelData['model'], $tmModelData['config']);
    $instance->setTimemachineModelInstance($tmModel);

    $tmSecondaryInstance = new timemachineEnabledSqlModel($modelData['schema'], $modelData['model'], $modelData['config']);
    $tmSecondaryInstance->setTimemachineModelInstance($tmModel);
    overrideableTimemachine::storeInstance($tmSecondaryInstance);
    return $instance;
  }

  /**
   * [getModel description]
   * @param  string               $model [description]
   * @return \codename\core\model        [description]
   */
  protected function getTimemachineEnabledModel(string $model): \codename\core\model {
    return static::getTimemachineEnabledModelStatic($model);
  }
}

/**
 * Overridden timemachine class
 * that allows setting an instance directly (and skip app::getModel internally)
 * - needed for these 'staged' unit tests
 */
class overrideableTimemachine extends \codename\core\timemachine {
  /**
   * [storeInstance description]
   * @param  \codename\core\model  $modelInstance [description]
   * @param  string $app           [description]
   * @param  string $vendor        [description]
   * @return [type]                [description]
   */
  public static function storeInstance(\codename\core\model $modelInstance, string $app = '', string $vendor = '') {
    $capableModelName = $modelInstance->getIdentifier();
    $identifier = $capableModelName.'-'.$vendor.'-'.$app;
    self::$instances[$identifier] = new self($modelInstance);
  }

}

class timemachineEnabledSqlModel extends \codename\core\tests\sqlModel
  implements \codename\core\model\timemachineInterface {

  /**
   * @inheritDoc
   */
  public function isTimemachineEnabled(): bool
  {
    return true;
  }

  /**
   * @inheritDoc
   */
  public function getTimemachineModel(): \codename\core\model
  {
    return $this->timemachineModelInstance; //  new timemachine();
  }

  protected $timemachineModelInstance = null;

  public function setTimemachineModelInstance(\codename\core\model\timemachineModelInterface $instance) {
    $this->timemachineModelInstance = $instance;
  }
}

class timemachineModel extends \codename\core\tests\sqlModel
  implements timemachineModelInterface {

  /**
   * @inheritDoc
   */
  public function save(array $data) : \codename\core\model
  {
    if($data[$this->getPrimarykey()]) {
      throw new exception('TIMEMACHINE_UPDATE_DENIED', exception::$ERRORLEVEL_FATAL);
    } else {
      $data = array_replace($data, $this->getIdentity());
      return parent::save($data);
    }
  }

  /**
   * current identity, null if not retrieved yet
   * @var array|null
   */
  protected $identity = null;

  /**
   * Get identity parameters for injecting
   * into the timemachine dataset
   * @return array
   */
  protected function getIdentity () : array {
    if(!$this->identity) {
      $this->identity = [
        'timemachine_source' => 'unittest',
        'timemachine_user_id' => 123,
      ];
    }
    return $this->identity;
  }

  /**
   * @inheritDoc
   */
  public function getModelField(): string
  {
    return 'timemachine_model';
  }

  /**
   * @inheritDoc
   */
  public function getRefField(): string
  {
    return 'timemachine_ref';
  }

  /**
   * @inheritDoc
   */
  public function getDataField(): string
  {
    return 'timemachine_data';
  }
}
