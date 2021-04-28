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

    static::createModel('vfields', 'customer', [
      'field' => [
        'customer_id',
        'customer_created',
        'customer_modified',
        'customer_no',
        'customer_person_id',
        'customer_person',
        'customer_contactentries',
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
        'customer_contactentries' => 'virtual'
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
   * [createTestData description]
   */
  protected static function createTestData(): void {

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
    $dataset = $model->load(1);
    $this->assertEquals(1, $dataset['testdata_id']);
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
      $childDataset = \codename\core\io\helper\deepaccess::get($dataset, $path);
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
    $this->assertEquals(2,$res[0]['testdata_id']);
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
   * Tests updating a structure field (simple)
   */
  public function testStructureData(): void {
    $model = $this->getModel('testdata');
    $testdata = $model->load(1);
    $model->save([
      $model->getPrimarykey() => $testdata[$model->getPrimarykey()],
      'testdata_structure'    => [ 'changed' => true ],
    ]);
    $updated = $model->load(1);
    $this->assertEquals([ 'changed' => true ], $updated['testdata_structure']);
    $model->save($testdata);
    $restored = $model->load(1);
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

    $this->addWarning('Aggregate filters with IN() / array as filter value is not supported yet - field type determination pending!');
    $this->expectException(\TypeError::class);

    // Aggregate Filter
    $testAggregateFilterMonthModel = $this->getModel('testdata');
    $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
    $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month1', [1, 3]);
    $testAggregateFilterMonthModel->addAggregateFilter('entries_month2', [1, 3]);

    // WARNING: sqlite doesn't support HAVING without GROUP BY
    $testAggregateFilterMonthModel->addGroup('testdata_id');

    $res = $testAggregateFilterMonthModel->search()->getResult();
    $this->assertEquals([3, 3, 3], array_column($res, 'entries_month1'));
    $this->assertEquals([3, 3, 3], array_column($res, 'entries_month2'));
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

    $testdataTm->save([
      'testdata_id'       => 1,
      'testdata_integer'  => 888,
    ]);

    $timemachine = new \codename\core\timemachine($testdataTm);
    $history = $timemachine->getHistory(1);

    $delta = $timemachine->getDeltaData(1, 0);
    $this->assertEquals([ 'testdata_integer' => 3], $delta);

    $bigbangState = $timemachine->getHistoricData(1, 0);
    $this->assertEquals(3, $bigbangState['testdata_integer']);

    // restore via delta
    $testdataTm->save(array_merge([
      'testdata_id'       => 1,
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
