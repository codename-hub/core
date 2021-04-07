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
      ],
      'primary' => [
        'testdata_id'
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
        'testdata_text'     => 'text',
        'testdata_date'     => 'text_date',
        'testdata_number'   => 'number',
        'testdata_integer'  => 'number_natural',
        'testdata_boolean'  => 'boolean',
        'testdata_structure'=> 'structure',
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
        ]
      ],
      'datatype' => [
        'person_id'         => 'number_natural',
        'person_created'    => 'text_timestamp',
        'person_modified'   => 'text_timestamp',
        'person_firstname'  => 'text',
        'person_lastname'   => 'text',
        'person_birthdate'  => 'text_date',
        'person_parent_id'  => 'number_natural',
        'person_parent'     => 'virtual'
      ],
      'connection' => 'default'
    ]);

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
    $id = $customerModel->lastInsertId();

    $dataset = $customerModel->load($id);

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
  }

  /**
   * tests a runtime-based virtual field
   */
  public function testVirtualFieldQuery() {
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
