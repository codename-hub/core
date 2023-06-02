<?php

namespace codename\core\tests\model;

use codename\core\app;
use codename\core\database;
use codename\core\exception;
use codename\core\helper\date;
use codename\core\helper\deepaccess;
use codename\core\model;
use codename\core\model\plugin\collection;
use codename\core\model\plugin\filter\dynamic;
use codename\core\model\plugin\join;
use codename\core\model\schematic\discreteDynamic;
use codename\core\model\schematic\sql;
use codename\core\model\timemachineInterface;
use codename\core\model\timemachineModelInterface;
use codename\core\test\overrideableApp;
use codename\core\tests\base;
use codename\core\tests\jsonModel;
use codename\core\tests\model\schematic\mysqlTest;
use codename\core\tests\sqlModel;
use codename\core\timemachine;
use codename\core\transaction;
use codename\core\value\text\modelfield;
use LogicException;
use PDOException;
use ReflectionException;
use stdClass;
use Throwable;

/**
 * Base model class performing cross-platform/technology tests with model classes
 */
abstract class abstractModelTest extends base
{
    /**
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public static function tearDownAfterClass(): void
    {
        static::deleteTestData();
        parent::tearDownAfterClass();
        static::$initialized = false;
        overrideableApp::reset();
    }

    /**
     * Deletes data that is created during createTestData()
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public static function deleteTestData(): void
    {
        $cleanupModels = [
          'testdata',
          'details',
          'moredetails',
          'timemachine',
          'table1',
          'table2',
        ];
        foreach ($cleanupModels as $modelName) {
            $model = static::getModelStatic($modelName);
            $model->addFilter($model->getPrimaryKey(), 0, '>')
              ->delete()->reset();

            // NOTE: we should not assert this in a static way
            // as it interferes with parallel or isolated test execution
            // and tests, that target doesNotPerformAssertions
            // static::assertEquals(0, $model->getCount());
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetConfigExplicitConnectionValid(): void
    {
        $model = $this->getModel('testdata');
        $model->setConfig('default', 'testschema', 'testdata');

        $dataset = $model->setLimit(1)->search()->getResult()[0];
        static::assertGreaterThanOrEqual(1, $dataset['testdata_id']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetConfigExplicitConnectionInvalid(): void
    {
        $this->expectException(exception::class);
        // TODO: right now we expect EXCEPTION_GETDATA_REQUESTEDKEYINTYPENOTFOUND message
        // but this might change soon
        $model = $this->getModel('testdata');
        $model->setConfig('nonexisting_connection', 'testschema', 'testdata');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetConfigInvalidValues(): void
    {
        $this->expectException(exception::class);
        // TODO: specify the exception message
        $model = $this->getModel('testdata');
        $model->setConfig('default', 'nonexisting_schema', 'nonexisting_model');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testModelconfigInvalidWithoutCreatedAndModifiedField(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(sql::EXCEPTION_MODEL_CONFIG_MISSING_FIELD);
        new sqlModel('nonexisting', 'without_created_and_modified', [
          'field' => [
            'without_created_and_modified_id',
          ],
          'primary' => [
            'without_created_and_modified_id',
          ],
          'datatype' => [
            'without_created_and_modified_id' => 'number_natural',
          ],
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testModelconfigInvalidWithoutModifiedField(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(sql::EXCEPTION_MODEL_CONFIG_MISSING_FIELD);
        new sqlModel('nonexisting', 'without_modified', [
          'field' => [
            'without_modified_id',
            'without_modified_created',
          ],
          'primary' => [
            'without_modified_id',
          ],
          'datatype' => [
            'without_modified_id' => 'number_natural',
            'without_modified_created' => 'text_timestamp',
          ],
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDeleteWithoutArgsWillFail(): void
    {
        //
        // ::delete() without given PKEY, nor filters, MUST FAIL.
        //
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED');
        $model = $this->getModel('testdata');
        $model->delete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testUpdateWithoutArgsWillFail(): void
    {
        //
        // ::update() without filters MUST FAIL.
        //
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_UPDATE_NO_FILTERS_DEFINED');
        $model = $this->getModel('testdata');
        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }
        $model->update([
          'testdata_integer' => 0,
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddCalculatedFieldExistsWillFail(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(model::EXCEPTION_ADDCALCULATEDFIELD_FIELDALREADYEXISTS);
        $this->getModel('testdata')
          ->addCalculatedField('testdata_integer', '(1+1)');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testHideFieldSingle(): void
    {
        $model = $this->getModel('testdata');
        $fields = $model->getFields();

        $visibleFields = array_filter($fields, function ($f) {
            return ($f != 'testdata_integer');
        });

        $model->hideField('testdata_integer');
        $res = $model->search()->getResult();

        static::assertCount(4, $res);
        foreach ($res as $r) {
            //
            // Make sure we don't get testdata_integer
            // but every other field
            //
            foreach ($visibleFields as $f) {
                static::assertArrayHasKey($f, $r);
            }
            static::assertArrayNotHasKey('testdata_integer', $r);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testHideFieldMultipleCommaTrim(): void
    {
        $model = $this->getModel('testdata');
        $fields = $model->getFields();

        $visibleFields = array_filter($fields, function ($f) {
            return ($f != 'testdata_integer') && ($f != 'testdata_text');
        });

        // Testing auto-split/explode and trim
        $model->hideField('testdata_integer, testdata_text');
        $res = $model->search()->getResult();

        static::assertCount(4, $res);
        foreach ($res as $r) {
            //
            // Make sure we don't get testdata_integer and testdata_text
            // but every other field
            //
            foreach ($visibleFields as $f) {
                static::assertArrayHasKey($f, $r);
            }
            static::assertArrayNotHasKey('testdata_integer', $r);
            static::assertArrayNotHasKey('testdata_text', $r);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testHideAllFieldsAddOne(): void
    {
        $model = $this->getModel('testdata');
        $res = $model
          ->hideAllFields()
          ->addField('testdata_integer')
          ->search()->getResult();
        static::assertCount(4, $res);
        foreach ($res as $r) {
            // Make sure 'testdata_integer' is the one and only field in the result datasets
            static::assertArrayHasKey('testdata_integer', $r);
            static::assertEquals(['testdata_integer'], array_keys($r));
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testHideAllFieldsAddMultiple(): void
    {
        $model = $this->getModel('testdata');
        $res = $model
          ->hideAllFields()
          ->addField('testdata_integer,testdata_text, testdata_number ') // internal trimming
          ->search()->getResult();
        static::assertCount(4, $res);
        foreach ($res as $r) {
            // Make sure 'testdata_integer' is the one and only field in the result datasets
            static::assertArrayHasKey('testdata_integer', $r);
            static::assertArrayHasKey('testdata_text', $r);
            static::assertArrayHasKey('testdata_number', $r);
            static::assertEquals(['testdata_integer', 'testdata_text', 'testdata_number'], array_keys($r));
        }
    }

    /**
     * WARNING: this tests a special edge case - which is almost not the desired state
     * but there's no better defined solution right now,
     * so we test for stability/behaviour
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldComplexEdgeCaseNoVfr(): void
    {
        $model = $this->getModel('testdata')
          ->addModel($detailsModel = $this->getModel('details'))
          ->addModel($moreDetailsModel = $this->getModel('moredetails'));

        $model->hideAllFields();
        $detailsModel->hideAllFields();
        $moreDetailsModel->hideAllFields();

        $model->addVirtualField('test', function ($dataset) {
            return 'value';
        });

        $res = $model->search()->getResult();

        $dataset = $res[0];

        // we expect all pkeys to exist
        static::assertArrayHasKey($model->getPrimaryKey(), $dataset);
        static::assertArrayHasKey($detailsModel->getPrimaryKey(), $dataset);
        static::assertArrayHasKey($moreDetailsModel->getPrimaryKey(), $dataset);

        // virtual field should not be there
        // as we have no VFR set
        static::assertArrayNotHasKey('test', $dataset);
    }

    /**
     * WARNING: this tests a special edge case - which is almost not the desired state
     * but there's no better defined solution right now,
     * so we test for stability/behaviour
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldComplexEdgeCasePartialVfr(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($detailsModel = $this->getModel('details'))
          ->addModel($moreDetailsModel = $this->getModel('moredetails'));

        $model->hideAllFields();
        $detailsModel->hideAllFields();
        $moreDetailsModel->hideAllFields();

        $model->addVirtualField('test', function ($dataset) {
            return 'value';
        });

        $res = $model->search()->getResult();

        $dataset = $res[0];

        // WARNING: edge case - ->hideAllFields on all models have been called
        // but only a virtual field on root model has been added
        // this gives us a strange situation/result - root model will 'disappear'
        // but the joins are kept, fully.
        static::assertArrayNotHasKey($model->getPrimaryKey(), $dataset);

        // those will be available
        static::assertArrayHasKey($detailsModel->getPrimaryKey(), $dataset);
        static::assertArrayHasKey($moreDetailsModel->getPrimaryKey(), $dataset);

        // this virtual field on root model should persist
        static::assertArrayHasKey('test', $dataset);
    }

    /**
     * WARNING: this tests a special edge case - which is almost not the desired state
     * but there's no better defined solution right now,
     * so we test for stability/behaviour
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldComplexEdgeCaseFullVfr(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($detailsModel = $this->getModel('details')->setVirtualFieldResult(true))
          ->addModel($moreDetailsModel = $this->getModel('moredetails')->setVirtualFieldResult(true));

        $model->hideAllFields();
        $detailsModel->hideAllFields();
        $moreDetailsModel->hideAllFields();

        $model->addVirtualField('test', function ($dataset) {
            return 'value';
        });

        $res = $model->search()->getResult();

        $dataset = $res[0];

        // WARNING: edge case - ->hideAllFields on all models have been called
        // but only a virtual field on root model has been added
        // this gives us a strange situation/result - root model will 'disappear'
        // but the joins are kept, fully.
        static::assertArrayNotHasKey($model->getPrimaryKey(), $dataset);

        // those will be available
        static::assertArrayHasKey($detailsModel->getPrimaryKey(), $dataset);
        static::assertArrayHasKey($moreDetailsModel->getPrimaryKey(), $dataset);

        // this virtual field on root model should persist
        static::assertArrayHasKey('test', $dataset);
    }

    /**
     * WARNING: this tests a special edge case - which is almost not the desired state
     * but there's no better defined solution right now,
     * so we test for stability/behaviour
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldComplexEdgeCaseRegularFieldFullVfr(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($detailsModel = $this->getModel('details')->setVirtualFieldResult(true))
          ->addModel($moreDetailsModel = $this->getModel('moredetails')->setVirtualFieldResult(true));

        $model->hideAllFields();
        $detailsModel->hideAllFields();
        $moreDetailsModel->hideAllFields();

        // only add one field, in this case: the PKEY of the root model
        $model->addField($model->getPrimaryKey());

        $res = $model->search()->getResult();

        $dataset = $res[0];

        // WARNING: edge case - ->hideAllFields on all models have been called,
        // and we only add a (regular) field on the root model
        // all fields, except the root model's field will disappear
        static::assertArrayHasKey($model->getPrimaryKey(), $dataset);

        // those will be available
        static::assertArrayNotHasKey($detailsModel->getPrimaryKey(), $dataset);
        static::assertArrayNotHasKey($moreDetailsModel->getPrimaryKey(), $dataset);
    }

    /**
     * WARNING: this tests a special edge case - which is almost not the desired state
     * but there's no better defined solution right now,
     * so we test for stability/behaviour
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldComplexEdgeCaseNestedRegularFieldFullVfr(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($detailsModel = $this->getModel('details')->setVirtualFieldResult(true))
          ->addModel($moreDetailsModel = $this->getModel('moredetails')->setVirtualFieldResult(true));

        $model->hideAllFields();
        $detailsModel->hideAllFields();
        $moreDetailsModel->hideAllFields();

        // only add one field, in this case: the PKEY of the root model
        $detailsModel->addField($detailsModel->getPrimaryKey());

        $res = $model->search()->getResult();

        $dataset = $res[0];

        // WARNING: edge case - ->hideAllFields on all models have been called,
        // and we only add a (regular) field on a *NESTED* model
        // all fields, except the joined model's field will disappear
        static::assertArrayHasKey($detailsModel->getPrimaryKey(), $dataset);

        // those will be available
        static::assertArrayNotHasKey($model->getPrimaryKey(), $dataset);
        static::assertArrayNotHasKey($moreDetailsModel->getPrimaryKey(), $dataset);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldFailsWithNonexistingField(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(model::EXCEPTION_ADDFIELD_FIELDNOTFOUND);
        $model = $this->getModel('testdata');
        $model->addField('testdata_nonexisting'); // We expect an early failure
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldFailsWithMultipleFieldsAndAliasProvided(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_ADDFIELD_ALIAS_ON_MULTIPLE_FIELDS');
        $model = $this->getModel('testdata');
        $model->addField('testdata_integer,testdata_text', 'some_alias'); // Obviously, this is a no-go.
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testHideAllFieldsAddAliasedField(): void
    {
        $model = $this->getModel('testdata');
        $res = $model
          ->hideAllFields()
          ->addField('testdata_integer', 'aliased_field')
          ->search()->getResult();
        static::assertCount(4, $res);
        foreach ($res as $r) {
            // Make sure 'aliased_field' is the one and only field in the result datasets
            static::assertArrayHasKey('aliased_field', $r);
            static::assertEquals(['aliased_field'], array_keys($r));
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSimpleModelJoin(): void
    {
        $model = $this->getModel('testdata')
          ->addModel($detailsModel = $this->getModel('details'));

        $originalDataset = [
          'testdata_number' => 3.3,
          'testdata_text' => 'some_dataset',
        ];

        $detailsModel->save([
          'details_data' => $originalDataset,
        ]);
        $detailsId = $detailsModel->lastInsertId();

        $model->save(
            array_merge(
                $originalDataset,
                ['testdata_details_id' => $detailsId]
            )
        );
        $id = $model->lastInsertId();

        $dataset = $model->load($id);
        static::assertEquals($originalDataset, $dataset['details_data']);

        foreach ($detailsModel->getFields() as $field) {
            if ($detailsModel->getConfig()->get('datatype>' . $field) == 'virtual') {
                // In this case, no vfields/handler, expect it to NOT appear.
                static::assertArrayNotHasKey($field, $dataset);
            } else {
                static::assertArrayHasKey($field, $dataset);
            }
        }
        foreach ($model->getFields() as $field) {
            static::assertArrayHasKey($field, $dataset);
        }

        $model->delete($id);
        $detailsModel->delete($detailsId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSimpleModelJoinWithVirtualFields(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($detailsModel = $this->getModel('details'));

        $originalDataset = [
          'testdata_number' => 3.3,
          'testdata_text' => 'some_dataset',
        ];

        $detailsModel->save([
          'details_data' => $originalDataset,
        ]);
        $detailsId = $detailsModel->lastInsertId();

        $model->save(
            array_merge(
                $originalDataset,
                ['testdata_details_id' => $detailsId]
            )
        );
        $id = $model->lastInsertId();

        $dataset = $model->load($id);

        static::assertEquals($originalDataset, $dataset['details_data']);

        foreach ($detailsModel->getFields() as $field) {
            if ($detailsModel->getConfig()->get('datatype>' . $field) == 'virtual') {
                // In this case, no vfields/handler, expect it to NOT appear.
                static::assertArrayNotHasKey($field, $dataset);
            } else {
                static::assertArrayHasKey($field, $dataset);
            }
        }
        foreach ($model->getFields() as $field) {
            static::assertArrayHasKey($field, $dataset);
        }

        // modify some model details
        $model->hideField('testdata_id');
        $detailsModel->hideField('details_created');
        $model->addField('testdata_id', 'root_level_alias');
        $detailsModel->addField('details_id', 'nested_alias');

        $dataset = $model->load($id);

        static::assertArrayNotHasKey('testdata_id', $dataset);
        static::assertArrayNotHasKey('details_created', $dataset);
        static::assertArrayHasKey('root_level_alias', $dataset);
        static::assertArrayHasKey('nested_alias', $dataset);

        static::assertEquals($id, $dataset['root_level_alias']);
        static::assertEquals($detailsId, $dataset['nested_alias']);

        $model->delete($id);
        $detailsModel->delete($detailsId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testConditionalJoin(): void
    {
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
              'person_country' => 'AT',
              'person_firstname' => 'Alex',
              'person_lastname' => 'Anderson',
              'person_birthdate' => '1978-02-03',
            ],
          ],
          [
            'customer_no' => 'A1001',
            'customer_person' => [
              'person_country' => 'AT',
              'person_firstname' => 'Bridget',
              'person_lastname' => 'Balmer',
              'person_birthdate' => '1981-11-15',
            ],
          ],
          [
            'customer_no' => 'A1002',
            'customer_person' => [
              'person_country' => 'DE',
              'person_firstname' => 'Christian',
              'person_lastname' => 'Crossback',
              'person_birthdate' => '1990-04-19',
            ],
          ],
          [
            'customer_no' => 'A1003',
            'customer_person' => [
              'person_country' => 'DE',
              'person_firstname' => 'Dodgy',
              'person_lastname' => 'Data',
              'person_birthdate' => null,
            ],
          ],
        ];

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        foreach ($datasets as $d) {
            $customerModel->saveWithChildren($d);
            $customerIds[] = $customerModel->lastInsertId();
            $personIds[] = $personModel->lastInsertId();
        }

        // w/o model_name + double conditions
        $model = $this->getModel('customer')
          ->addCustomJoin(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_person_id',
              'person_id',
              [
                  // will default to the higher-level model
                ['field' => 'customer_no', 'operator' => '>=', 'value' => '\'A1001\''],
                ['field' => 'customer_no', 'operator' => '<=', 'value' => '\'A1002\''],
              ]
          );
        $model->addOrder('customer_no'); // make sure to have the right order, see below
        $model->saveLastQuery = true;
        $res = $model->search()->getResult();
        static::assertCount(4, $res);
        static::assertEquals([null, 'AT', 'DE', null], array_column($res, 'person_country'));

        // using model_name
        $model = $this->getModel('customer')
          ->addCustomJoin(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_person_id',
              'person_id',
              [
                ['model_name' => 'person', 'field' => 'person_country', 'operator' => '=', 'value' => '\'DE\''],
              ]
          );
        $model->addOrder('customer_no'); // make sure to have the right order, see below
        $model->saveLastQuery = true;
        $res = $model->search()->getResult();
        static::assertCount(4, $res);
        static::assertEquals([null, null, 'DE', 'DE'], array_column($res, 'person_country'));

        // null value condition
        $model = $this->getModel('customer')
          ->addCustomJoin(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_person_id',
              'person_id',
              [
                ['model_name' => 'person', 'field' => 'person_birthdate', 'operator' => '=', 'value' => null],
              ]
          );
        $model->addOrder('customer_no'); // make sure to have the right order, see below
        $model->saveLastQuery = true;
        $res = $model->search()->getResult();
        static::assertCount(4, $res);
        static::assertEquals([null, null, null, 'DE'], array_column($res, 'person_country'));


        foreach ($customerIds as $id) {
            $customerModel->delete($id);
        }
        foreach ($personIds as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testConditionalJoinFail(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_JOIN_CONDITION_MODEL_NAME');
        $model = $this->getModel('customer')
          ->addCustomJoin(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_person_id',
              'person_id',
              [
                  // non-associated model...
                ['model_name' => 'testdata', 'field' => 'testdata_number', 'operator' => '!=', 'value' => null],
              ]
          );
        $model->addOrder('customer_no'); // make sure to have the right order, see below
        $model->saveLastQuery = true;
        $model->search()->getResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testReverseJoinEquality(): void
    {
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
              'person_country' => 'AT',
              'person_firstname' => 'Alex',
              'person_lastname' => 'Anderson',
              'person_birthdate' => '1978-02-03',
            ],
          ],
          [
            'customer_no' => 'A1001',
            'customer_person' => [
              'person_country' => 'AT',
              'person_firstname' => 'Bridget',
              'person_lastname' => 'Balmer',
              'person_birthdate' => '1981-11-15',
            ],
          ],
          [
            'customer_no' => 'A1002',
            'customer_person' => [
              'person_country' => 'DE',
              'person_firstname' => 'Christian',
              'person_lastname' => 'Crossback',
              'person_birthdate' => '1990-04-19',
            ],
          ],
          [
            'customer_no' => 'A1003',
            'customer_person' => [
              'person_country' => 'DE',
              'person_firstname' => 'Dodgy',
              'person_lastname' => 'Data',
              'person_birthdate' => null,
            ],
          ],
        ];

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        foreach ($datasets as $d) {
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

        static::assertCount(4, $resForward);
        static::assertEquals($resForward, $resReverse);

        foreach ($customerIds as $id) {
            $customerModel->delete($id);
        }
        foreach ($personIds as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testReplace(): void
    {
        $ids = [];
        $model = $this->getModel('customer');

        if (!($this instanceof mysqlTest)) {
            static::markTestIncomplete('Upsert is working differently on this platform - not implemented yet!');
        }

        $model->save([
          'customer_no' => 'R1000',
          'customer_notes' => 'Replace me',
        ]);
        $ids[] = $firstId = $model->lastInsertId();

        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }

        $model->replace([
          'customer_no' => 'R1000',
          'customer_notes' => 'Replaced',
        ]);

        $dataset = $model->load($firstId);
        static::assertEquals('Replaced', $dataset['customer_notes']);

        foreach ($ids as $id) {
            $model->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testMultiComponentForeignKeyJoin(): void
    {
        $table1 = $this->getModel('table1');
        $table2 = $this->getModel('table2');

        $table1->save([
          'table1_key1' => 'first',
          'table1_key2' => 1,
          'table1_value' => 'table1',
        ]);
        $table2->save([
          'table2_key1' => 'first',
          'table2_key2' => 1,
          'table2_value' => 'table2',
        ]);
        $table1->save([
          'table1_key1' => 'arbitrary',
          'table1_key2' => 2,
          'table1_value' => 'not in table2',
        ]);
        $table2->save([
          'table2_key1' => 'arbitrary',
          'table2_key2' => 3,
          'table2_value' => 'not in table1',
        ]);

        $table1->addModel($table2);
        $res = $table1->search()->getResult();

        static::assertCount(2, $res);
        static::assertEquals('table1', $res[0]['table1_value']);
        static::assertEquals('table2', $res[0]['table2_value']);

        static::assertEquals('not in table2', $res[1]['table1_value']);
        static::assertEquals(null, $res[1]['table2_value']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testTimemachineHistory(): void
    {
        $model = $this->getTimemachineEnabledModel('testdata');
        $model->save([
          'testdata_text' => 'tm_history',
          'testdata_integer' => 5555,
        ]);
        $tsAtCreation = date::getCurrentTimestamp();
        $id = $model->lastInsertId();
        static::assertNotEmpty($model->load($id));

        $timemachine = new timemachine($model);

        static::assertEmpty($timemachine->getDeltaData($id, 0), 'Timemachine deltas should be empty at this point');
        static::assertEmpty($timemachine->getHistory($id), 'Timemachine history should be empty at this point');

        // Emulate next second.
        sleep(1);

        $model->save([
          'testdata_id' => $id,
          'testdata_integer' => 5556,
        ]);
        date::getCurrentTimestamp();

        // Emulate next second.
        sleep(1);
        $tsAfterUpdate = date::getCurrentTimestamp();

        static::assertEmpty($timemachine->getDeltaData($id, $tsAfterUpdate), 'Timemachine deltas should be empty due to late reference TS');
        static::assertNotEmpty($timemachine->getDeltaData($id, $tsAtCreation), 'Timemachine deltas should include the history');

        static::assertEquals(5555, $timemachine->getHistoricData($id, $tsAtCreation)['testdata_integer']);

        $tsBeforeDelete = date::getCurrentTimestamp();

        $model->delete($id);
        static::assertEmpty($model->load($id));

        // Ensure we have an entry for a deleted record
        static::assertEquals(5556, $timemachine->getHistoricData($id, $tsBeforeDelete)['testdata_integer']);
    }

    /**
     * @param string $model [description]
     * @return model        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getTimemachineEnabledModel(string $model): model
    {
        return static::getTimemachineEnabledModelStatic($model);
    }

    /**
     * @param string $model [description]
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    protected static function getTimemachineEnabledModelStatic(string $model): model
    {
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
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDeleteSinglePkeyTimemachineEnabled(): void
    {
        $model = $this->getTimemachineEnabledModel('testdata');
        $model->save([
          'testdata_text' => 'single_pkey_delete',
          'testdata_integer' => 1234,
        ]);
        $id = $model->lastInsertId();
        static::assertNotEmpty($model->load($id));
        $model->delete($id);
        static::assertEmpty($model->load($id));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testBulkUpdateAndDelete(): void
    {
        $model = $this->getModel('testdata');
        $this->testBulkUpdateAndDeleteUsingModel($model);
    }

    /**
     * [testBulkUpdateAndDeleteUsingModel description]
     * @param model $model [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testBulkUpdateAndDeleteUsingModel(model $model): void
    {
        // $model = $this->getModel('testdata');

        // create example dataset
        $ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $model->save([
              'testdata_text' => 'bulkdata_test',
              'testdata_integer' => $i,
              'testdata_structure' => [
                'some_key' => 'some_value',
              ],
            ]);
            $ids[] = $model->lastInsertId();
        }

        $model
          ->addFilter('testdata_text', 'bulkdata_test');

        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }

        // update those entries (not by PKEY)
        $model
          ->update([
            'testdata_integer' => 333,
            'testdata_number' => 12.34, // additional update data in this field not used before
            'testdata_structure' => [
              'some_key' => 'some_value',
              'some_new_key' => 'some_new_value',
            ],
          ]);

        // compare data
        foreach ($ids as $id) {
            $dataset = $model->load($id);
            static::assertEquals('bulkdata_test', $dataset['testdata_text']);
            static::assertEquals(333, $dataset['testdata_integer']);
        }

        // delete them
        $model
          ->addFilter($model->getPrimaryKey(), $ids)
          ->delete();

        // make sure they don't exist anymore
        $res = $model->addFilter($model->getPrimaryKey(), $ids)->search()->getResult();
        static::assertEmpty($res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testBulkUpdateAndDeleteTimemachineEnabled(): void
    {
        $model = $this->getTimemachineEnabledModel('testdata');
        $this->testBulkUpdateAndDeleteUsingModel($model);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testRecursiveModelJoin(): void
    {
        $personModel = $this->getModel('person');

        $datasets = [
          [
              // Top, no parent
            'person_firstname' => 'Ella',
            'person_lastname' => 'Campbell',
          ],
          [
              // 1st level down
            'person_firstname' => 'Harry',
            'person_lastname' => 'Sanders',
          ],
          [
              // 2nd level down
            'person_firstname' => 'Stephen',
            'person_lastname' => 'Perkins',
          ],
          [
              // 3rd level down, no more children
            'person_firstname' => 'Michael',
            'person_lastname' => 'Vaughn',
          ],
        ];

        $ids = [];

        $parentId = null;
        foreach ($datasets as $dataset) {
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
                ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Vaughn'],
              ],
              join::TYPE_INNER,
              'person_id',
              'person_parent_id'
          );
        $recursiveModel->addFilter('person_lastname', 'Sanders');
        $res = $queryModel->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals('Vaughn', $res[0]['person_lastname']);

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
              join::TYPE_INNER,
              'person_id',
              'person_parent_id'
          );
        $traverseUpModel->addFilter('person_lastname', 'Perkins');
        $res = $traverseUpModel->search()->getResult();

        static::assertCount(3, $res);
        // NOTE: order is not guaranteed, therefore: just compare item presence
        static::assertEqualsCanonicalizing([
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
              join::TYPE_INNER,
              'person_id',
              'person_parent_id'
          );
        $traverseDownModel->addFilter('person_lastname', 'Perkins');
        $res = $traverseDownModel->search()->getResult();
        static::assertCount(2, $res);
        // NOTE: order is not guaranteed, therefore: just compare item presence
        static::assertEqualsCanonicalizing(['Stephen', 'Michael'], array_column($res, 'person_firstname'));

        //
        // Root-level traverse up
        //
        $rootTraverseUpModel = $this->getModel('person')
          ->setRecursive(
              'person_parent_id',
              'person_id',
              [
                  // Single anchor condition
                ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
              ]
          );
        $res = $rootTraverseUpModel->search()->getResult();
        static::assertCount(2, $res);
        // NOTE: order is not guaranteed, therefore: just compare item presence
        static::assertEqualsCanonicalizing(['Harry', 'Ella'], array_column($res, 'person_firstname'));

        //
        // Root-level traverse down
        //
        $rootTraverseDownModel = $this->getModel('person')
          ->setRecursive(
              'person_id',
              'person_parent_id',
              [
                  // Single anchor condition
                ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
              ]
          );
        $res = $rootTraverseDownModel->search()->getResult();
        static::assertCount(3, $res);
        // NOTE: order is not guaranteed, therefore: just compare item presence
        static::assertEqualsCanonicalizing(['Harry', 'Stephen', 'Michael'], array_column($res, 'person_firstname'));

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
                new dynamic(modelfield::getInstance('person_lastname'), 'Sanders', '='),
              ]
          );
        $res = $rootTraverseDownUsingFilterInstanceModel->search()->getResult();
        static::assertCount(3, $res);
        // NOTE: order is not guaranteed, therefore: just compare item presence
        static::assertEqualsCanonicalizing(['Harry', 'Stephen', 'Michael'], array_column($res, 'person_firstname'));

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
                ]),
              join::TYPE_INNER
          );

        $joinedRecursiveModel->addFilter('person_lastname', 'Vaughn');
        $res = $joinedRecursiveModel->search()->getResult();
        static::assertCount(3, $res);
        static::assertEquals(['Vaughn'], array_unique(array_column($res, 'main_lastname')));

        // NOTE: databases might behave differently regarding order
        //
        // e.g. SQLite: see https://www.sqlite.org/lang_with.html:
        // "If there is no ORDER BY clause, then the order in which rows are extracted is undefined."
        // SQLite is mostly doing FIFO.
        //
        static::assertEqualsCanonicalizing(['Ella', 'Harry', 'Stephen'], array_column($res, 'person_firstname'));

        foreach (array_reverse($ids) as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * Tests whether calling setRecursive a second time will throw an exception
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetRecursiveTwiceWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SETRECURSIVE_ALREADY_ENABLED');

        $model = $this->getModel('person');
        for ($i = 1; $i <= 2; $i++) {
            $model->setRecursive(
                'person_parent_id',
                'person_id',
                [
                    // Single anchor condition
                  ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
                ]
            );
        }
    }

    /**
     * Tests whether setRecursive will throw an exception
     * if an undefined relation is used as recursion parameter
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetRecursiveInvalidConfigWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_RECURSIVE_MODEL_CONFIG');

        $model = $this->getModel('person');
        $model->setRecursive(
            'person_firstname',
            'person_id',
            [
                // Single anchor condition
              ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
            ]
        );
    }

    /**
     * Tests whether setRecursive throws an exception
     * if a nonexisting field is provided in the configuration
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSetRecursiveNonexistingFieldWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_RECURSIVE_MODEL_CONFIG');

        $model = $this->getModel('person');
        $model->setRecursive(
            'person_nonexisting',
            'person_id',
            [
                // Single anchor condition
              ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
            ]
        );
    }

    /**
     * Tests whether addRecursiveModel throws an exception
     * if an invalid/nonexisting field is provided in the configuration
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddRecursiveModelNonexistingFieldWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_RECURSIVE_MODEL_JOIN');

        $model = $this->getModel('person');
        $model->addRecursiveModel(
            $this->getModel('person'),
            'person_nonexisting',
            'person_id',
            [
                // Single anchor condition
              ['field' => 'person_lastname', 'operator' => '=', 'value' => 'Sanders'],
            ]
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFiltercollectionValueArray(): void
    {
        // Filtercollection with an array as filter value
        // (e.g. IN-query)
        $model = $this->getModel('testdata');

        $model->addFilterCollection([
          ['field' => 'testdata_text', 'operator' => '=', 'value' => ['foo']],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);
        static::assertEquals([3.14, 5.36], array_column($res, 'testdata_number'));

        $model->addFilterCollection([
          ['field' => 'testdata_text', 'operator' => '!=', 'value' => ['foo']],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);
        static::assertEquals([4.25, 0.99], array_column($res, 'testdata_number'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDefaultFiltercollectionValueArray(): void
    {
        // Filtercollection with an array as filter value
        // (e.g. IN-query)
        $model = $this->getModel('testdata');

        $model->addDefaultFilterCollection([
          ['field' => 'testdata_text', 'operator' => '=', 'value' => ['foo']],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);
        static::assertEquals([3.14, 5.36], array_column($res, 'testdata_number'));

        // as we've added a default FC (and nothing else)
        // searching second time should yield the same resultset
        static::assertEquals($res, $model->search()->getResult());
    }

    /**
     * Tests performing a regular left join
     * using forced virtual joining with no dataset available/set
     * to return a nulled/empty child dataset
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testLeftJoinForcedVirtualNoReferenceDataset(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel(
              $personModel = $this->getModel('person')->setVirtualFieldResult(true)
                ->setForceVirtualJoin(true),
          );

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        $customerModel->saveWithChildren([
          'customer_no' => 'join_fv_nochild',
            // No customer_person provided
        ]);

        $customerId = $customerModel->lastInsertId();

        // make sure to only find one result
        // (one entry that has both datasets)
        $dataset = $customerModel->load($customerId);

        static::assertEquals('join_fv_nochild', $dataset['customer_no']);
        static::assertNotEmpty($dataset['customer_person']);
        foreach ($personModel->getFields() as $field) {
            if ($personModel->getConfig()->get('datatype>' . $field) == 'virtual') {
                //
                // NOTE: we have no child models added,
                // and we expect the result to NOT have those (virtual) fields at all
                //
                static::assertArrayNotHasKey($field, $dataset['customer_person']);
            } else {
                // Expect the key(s) to exist, but be null.
                static::assertArrayHasKey($field, $dataset['customer_person']);
                static::assertNull($dataset['customer_person'][$field]);
            }
        }


        //
        // Test again using no VFR and varying FVJ states
        //
        $forceVirtualJoinStates = [true, false];

        foreach ($forceVirtualJoinStates as $fvjState) {
            $noVfrCustomerModel = $this->getModel('customer')->setVirtualFieldResult(false)
              ->addModel(
                  $noVfrPersonModel = $this->getModel('person')->setVirtualFieldResult(false)
                    ->setForceVirtualJoin($fvjState),
              );

            $datasetNoVfr = $noVfrCustomerModel->load($customerId);

            static::assertEquals('join_fv_nochild', $datasetNoVfr['customer_no']);
            static::assertArrayNotHasKey('customer_person', $datasetNoVfr);
            foreach ($noVfrPersonModel->getFields() as $field) {
                if ($noVfrPersonModel->getConfig()->get('datatype>' . $field) == 'virtual') {
                    //
                    // NOTE: we have no child models added,
                    // and we expect the result to NOT have those (virtual) fields at all
                    //
                    static::assertArrayNotHasKey($field, $datasetNoVfr);
                } else {
                    // Expect the key(s) to exist, but be null.
                    static::assertArrayHasKey($field, $datasetNoVfr);
                    static::assertNull($datasetNoVfr[$field]);
                }
            }
        }


        $customerModel->delete($customerId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInnerJoinRegular(): void
    {
        $this->testInnerJoin(false);
    }

    /**
     * @param bool $forceVirtualJoin [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInnerJoin(bool $forceVirtualJoin): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel(
              $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          );

        $customerIds = [];
        $personIds = [];

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        $customerModel->saveWithChildren([
          'customer_no' => 'join1',
          'customer_person' => [
            'person_firstname' => 'Some',
            'person_lastname' => 'Join',
          ],
        ]);

        $customerIds[] = $customerModel->lastInsertId();
        $personIds[] = $personModel->lastInsertId();

        $customerModel->saveWithChildren([
          'customer_no' => 'join2',
          'customer_person' => null,
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
              join::TYPE_INNER
          );

        // make sure to only find one result
        // (one entry that has both datasets)
        $innerJoinRes = $innerJoinModel->search()->getResult();
        static::assertCount(1, $innerJoinRes);

        // compare to regular result (left join)
        $res = $customerModel->search()->getResult();
        static::assertCount(2, $res);

        foreach ($customerIds as $id) {
            $customerModel->delete($id);
        }
        foreach ($personIds as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInnerJoinForcedVirtualJoin(): void
    {
        $this->testInnerJoin(true);
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
     * customer_person does not exist and neither does "person_country"
     * but the join is tried anyway.
     * We're throwing an exception this case,
     * as it is an indicator for incomplete code, missing definition
     * or even legacy code.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinVirtualFieldResultEnabledMissingVKey(): void
    {
        $customerModel = $this->getModel('customer')
          ->setVirtualFieldResult(true)
          ->hideAllFields()
          ->addField('customer_no')
          ->addModel(
              $personModel = $this->getModel('person')
                ->addModel($this->getModel('country'))
          );

        $personModel->save([
          'person_firstname' => 'john',
          'person_lastname' => 'doe',
          'person_country' => 'DE',
        ]);
        $personId = $personModel->lastInsertId();
        $customerModel->save([
          'customer_no' => 'missing_vkey',
          'customer_person_id' => $personId,
        ]);
        $customerId = $customerModel->lastInsertId();

        $dataset = $customerModel->load($customerId);
        static::assertArrayHasKey('customer_person', $dataset);
        static::assertEquals('john', $dataset['customer_person']['person_firstname']);
        static::assertEquals('Germany', $dataset['customer_person']['country_name']);

        //
        // NOTE: this is still pending clearance. For now, this emulates the old behaviour.
        // VFR keys are added implicitly
        //
        // try {
        //   $dataset = $customerModel->load($customerId);
        //   static::fail('Dataset loaded without exception to be fired - should crash.');
        // } catch (\codename\core\exception $e) {
        //   // NOTE: we only catch this specific exception!
        //   static::assertEquals('EXCEPTION_MODEL_PERFORMBAREJOIN_MISSING_VKEY', $e->getMessage());
        // }

        $customerModel->delete($customerId);
        $personModel->delete($personId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinVirtualFieldResultEnabledCustomVKey(): void
    {
        $customerModel = $this->getModel('customer')
          ->setVirtualFieldResult(true)
          ->addModel(
              $personModel = $this->getModel('person')
                ->addModel($this->getModel('country'))
          );

        $personModel->save([
          'person_firstname' => 'john',
          'person_lastname' => 'doe',
          'person_country' => 'DE',
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
        static::assertArrayNotHasKey('customer_person', $dataset);
        static::assertArrayHasKey('custom_vfield', $dataset);
        static::assertEquals('john', $dataset['custom_vfield']['person_firstname']);
        static::assertEquals('Germany', $dataset['custom_vfield']['country_name']);

        // NOTE: see testJoinVirtualFieldResultEnabledMissingVKey

        $customerModel->delete($customerId);
        $personModel->delete($personId);
    }

    /**
     * Tests a special case of model renormalization
     * no virtual field results enabled, two models on same nesting level (root)
     * with one or more hidden fields (each?)
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinHiddenFieldsNoVirtualFieldResult(): void
    {
        $customerModel = $this->getModel('customer')
          ->hideField('customer_no')
          ->addModel(
              $personModel = $this->getModel('person')
                ->hideField('person_firstname')
          );

        $personModel->save([
          'person_firstname' => 'john',
          'person_lastname' => 'doe',
        ]);
        $personId = $personModel->lastInsertId();
        $customerModel->save([
          'customer_no' => 'no_vfr',
          'customer_person_id' => $personId,
        ]);
        $customerId = $customerModel->lastInsertId();

        $dataset = $customerModel->load($customerId);
        static::assertEquals('doe', $dataset['person_lastname']);
        static::assertEquals($personId, $dataset['customer_person_id']);
        static::assertArrayNotHasKey('person_firstname', $dataset);
        static::assertArrayNotHasKey('customer_no', $dataset);

        $customerModel->delete($customerId);
        $personModel->delete($personId);
    }

    /**
     * Tests equally named fields in a joined model
     * to be re-normalized correctly
     * NOTE: this is SQL syntax and might be erroneous on non-sql models
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSameNamedCalculatedFieldsInVirtualFieldResults(): void
    {
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->addCalculatedField('calcfield', '(1+1)')
          ->addModel(
              $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
                ->addCalculatedField('calcfield', '(2+2)')
          );

        if (!($personModel instanceof sql)) {
            static::fail('setup fail');
        }

        $personModel->saveWithChildren([
          'person_firstname' => 'theFirstname',
          'person_lastname' => 'theLastName',
          'person_parent' => [
            'person_firstname' => 'parentFirstname',
            'person_lastname' => 'parentLastName',
          ],
        ]);

        $personId = $personModel->lastInsertId();
        $parentPersonId = $parentPersonModel->lastInsertId();

        $dataset = $personModel->load($personId);
        static::assertEquals(2, $dataset['calcfield']);
        static::assertEquals(4, $dataset['person_parent']['calcfield']);

        $personModel->delete($personId);
        $parentPersonModel->delete($parentPersonId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testRecursiveModelVirtualFieldDisabledWithAliasedFields(): void
    {
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

        if (!($personModel instanceof sql)) {
            static::fail('setup fail');
        }

        $personModel->saveWithChildren([
          'person_firstname' => 'theFirstname',
          'person_lastname' => 'theLastName',
          'person_parent' => [
            'person_firstname' => 'parentFirstname',
            'person_lastname' => 'parentLastName',
          ],
        ]);

        // NOTE: Important, disable for the following step.
        // (disabling vfields)
        $personModel->setVirtualFieldResult(false);

        $personId = $personModel->lastInsertId();
        $parentPersonId = $parentPersonModel->lastInsertId();

        $dataset = $personModel->load($personId);
        static::assertEquals([
          'person_firstname' => 'theFirstname',
          'person_lastname' => 'theLastName',
          'parent_firstname' => 'parentFirstname',
          'parent_lastname' => 'parentLastName',
        ], $dataset);

        $personModel->delete($personId);
        $parentPersonModel->delete($parentPersonId);
    }

    /**
     * Tests whether all identifiers and references to behave as designed
     * E.g. a child PKEY is authoritative over a given FKEY reference
     * in the parent model's dataset
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSaveWithChildrenAuthoritativeDatasetsAndIdentifiers(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel($personModel = $this->getModel('person'));

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        $customerModel->saveWithChildren([
          'customer_no' => 'X000',
          'customer_person' => [
            'person_firstname' => 'XYZ',
            'person_lastname' => 'ASD',
          ],
        ]);

        $customerId = $customerModel->lastInsertId();
        $personId = $personModel->lastInsertId();

        $customerModel->saveWithChildren([
          'customer_id' => $customerId,
          'customer_person' => [
            'person_id' => $personId,
            'person_firstname' => 'VVV',
          ],
        ]);

        $dataset = $customerModel->load($customerId);
        static::assertEquals($personId, $dataset['customer_person_id']);

        // create a secondary, unassociated person

        $personModel->save([
          'person_firstname' => 'otherX',
          'person_lastname' => 'otherY',
        ]);
        $otherPersonId = $personModel->lastInsertId();
        $customerModel->saveWithChildren([
          'customer_id' => $customerId,
          'customer_person' => [
            'person_id' => $otherPersonId,
            'person_firstname' => 'changed',
          ],
        ]);

        $dataset = $customerModel->load($customerId);
        static::assertEquals($otherPersonId, $dataset['customer_person_id']);

        $customerModel->saveWithChildren([
          'customer_id' => $customerId,
          'customer_person' => [
            'person_firstname' => 'another',
          ],
        ]);
        $anotherPersonId = $personModel->lastInsertId();
        $dataset = $customerModel->load($customerId);

        //
        // Make sure we have created another person (child dataset) implicitly
        //
        static::assertNotEquals($otherPersonId, $dataset['customer_person_id']);

        // make sure child PKEY (if given)
        // overrides the parent's FKEY value
        $customerModel->saveWithChildren([
          'customer_id' => $customerId,
          'customer_person_id' => $personId,
          'customer_person' => [
            'person_id' => $otherPersonId,
          ],
        ]);

        $dataset = $customerModel->load($customerId);
        static::assertEquals($otherPersonId, $dataset['customer_person_id']);

        // Cleanup
        $customerModel->delete($customerId);
        $personModel->delete($personId);
        $personModel->delete($otherPersonId);
        $personModel->delete($anotherPersonId);
    }

    /**
     * Tests a complex case of joining and model renormalization
     * (e.g. recursive models joined, but different fieldlists!)
     * In this case, a forced virtual join comes in-between.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testComplexVirtualRenormalizeForcedVirtualJoin(): void
    {
        $this->testComplexVirtualRenormalize(true);
    }

    /**
     * @param bool $forceVirtualJoin [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testComplexVirtualRenormalize(bool $forceVirtualJoin): void
    {
        $personModel = $this->getModel('person')->setVirtualFieldResult(true)
          ->hideField('person_lastname')
          ->addModel(
              // Parent optionally as forced virtual
              $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
                ->hideField('person_firstname')
                ->setForceVirtualJoin($forceVirtualJoin)
          );

        if (!($personModel instanceof sql)) {
            static::fail('setup fail');
        }

        $personModel->saveWithChildren([
          'person_firstname' => 'theFirstname',
          'person_lastname' => 'theLastName',
          'person_parent' => [
            'person_firstname' => 'parentFirstname',
            'person_lastname' => 'parentLastName',
          ],
        ]);

        $personId = $personModel->lastInsertId();
        $parentPersonId = $parentPersonModel->lastInsertId();

        $dataset = $personModel->load($personId);

        static::assertArrayNotHasKey('person_lastname', $dataset);
        static::assertArrayNotHasKey('person_firstname', $dataset['person_parent']);

        // re-add the hidden fields aliased
        $personModel->addField('person_lastname', 'aliased_lastname');
        $parentPersonModel->addField('person_firstname', 'aliased_firstname');
        $dataset = $personModel->load($personId);
        static::assertEquals('theLastName', $dataset['aliased_lastname']);
        static::assertEquals('parentFirstname', $dataset['person_parent']['aliased_firstname']);

        // add the alias fields to the respective other models
        // (aliased vfield renormalization)
        $parentPersonModel->addField('person_lastname', 'aliased_lastname');
        $personModel->addField('person_firstname', 'aliased_firstname');
        $dataset = $personModel->load($personId);
        static::assertEquals('theFirstname', $dataset['aliased_firstname']);
        static::assertEquals('theLastName', $dataset['aliased_lastname']);
        static::assertEquals('parentFirstname', $dataset['person_parent']['aliased_firstname']);
        static::assertEquals('parentLastName', $dataset['person_parent']['aliased_lastname']);

        $personModel->delete($personId);
        $parentPersonModel->delete($parentPersonId);
    }

    /**
     * Tests a complex case of joining and model renormalization
     * (e.g. recursive models joined, but different fieldlists!)
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testComplexVirtualRenormalizeRegular(): void
    {
        $this->testComplexVirtualRenormalize(false);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testComplexJoin(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel(
              $personModel = $this->getModel('person')->setVirtualFieldResult(true)
                ->addVirtualField('person_fullname1', function ($dataset) {
                    return $dataset['person_firstname'] . ' ' . $dataset['person_lastname'];
                })
                ->addModel($this->getModel('country'))
                ->addModel(
                    // Parent as forced virtual
                    $parentPersonModel = $this->getModel('person')->setVirtualFieldResult(true)
                      ->addVirtualField('person_fullname2', function ($dataset) {
                          return $dataset['person_firstname'] . ' ' . $dataset['person_lastname'];
                      })
                      ->setForceVirtualJoin(true)
                      ->addModel($this->getModel('country'))
                )
          );

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $customerModel->saveWithChildren([
          'customer_no' => 'COMPLEX1',
          'customer_person' => [
            'person_firstname' => 'Johnny',
            'person_lastname' => 'Doenny',
            'person_birthdate' => '1950-04-01',
            'person_country' => 'AT',
            'person_parent' => [
              'person_firstname' => 'Johnnys',
              'person_lastname' => 'Father',
              'person_birthdate' => '1930-12-10',
              'person_country' => 'DE',
            ],
          ],
        ]);

        $customerId = $customerModel->lastInsertId();
        $personId = $personModel->lastInsertId();
        $parentPersonId = $parentPersonModel->lastInsertId();

        $dataset = $customerModel->load($customerId);

        static::assertEquals('COMPLEX1', $dataset['customer_no']);
        static::assertEquals('Doenny', $dataset['customer_person']['person_lastname']);
        static::assertEquals('Austria', $dataset['customer_person']['country_name']);
        static::assertEquals('Father', $dataset['customer_person']['person_parent']['person_lastname']);
        static::assertEquals('Germany', $dataset['customer_person']['person_parent']['country_name']);

        static::assertEquals('Johnny Doenny', $dataset['customer_person']['person_fullname1']);
        static::assertEquals('Johnnys Father', $dataset['customer_person']['person_parent']['person_fullname2']);

        // make sure there are no other fields on the root level
        $intersect = array_intersect(array_keys($dataset), $customerModel->getFields());
        static::assertEmpty(array_diff(array_keys($dataset), $intersect));

        $customerModel->delete($customerId);
        $personModel->delete($personId);
        $parentPersonModel->delete($parentPersonId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingLimitExceededWillFail(): void
    {
        $this->expectException(PDOException::class);
        // exhaust the join nesting limit
        $model = $this->joinRecursively('person', $this->getJoinNestingLimit());
        $model->search()->getResult();
    }

    /**
     * Joins a model (itself) recursively (as far as possible)
     * @param string $modelName [model used for joining recursively]
     * @param int $limit [amount of joins performed]
     * @param bool $virtualFieldResult [whether to switch on vFieldResults by default]
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    protected function joinRecursively(string $modelName, int $limit, bool $virtualFieldResult = false): model
    {
        $model = $this->getModel($modelName)->setVirtualFieldResult($virtualFieldResult);
        $currentModel = $model;
        for ($i = 0; $i < $limit; $i++) {
            $recurseModel = $this->getModel($modelName)->setVirtualFieldResult($virtualFieldResult);
            $currentModel->addModel($recurseModel);
            $currentModel = $recurseModel;
        }
        return $model;
    }

    /**
     * Maximum (expected) join limit
     * @return int [description]
     */
    abstract protected function getJoinNestingLimit(): int;

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingLimitMaxxedOut(): void
    {
        $this->expectNotToPerformAssertions();
        // Try to max-out the join nesting limit (limit - 1)
        $model = $this->joinRecursively('person', $this->getJoinNestingLimit() - 1);
        $model->search()->getResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingLimitMaxxedOutSaving(): void
    {
        $this->testJoinNestingLimit();
    }

    /**
     * @param int|null $exceedLimit [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingLimit(?int $exceedLimit = null): void
    {
        $limit = $this->getJoinNestingLimit() - 1;

        $model = $this->joinRecursively('person', $limit, true);

        $deeperModel = null;
        if ($exceedLimit) {
            $currentJoin = $model->getNestedJoins('person')[0] ?? null;
            $deeplyNestedJoin = $currentJoin;
            while ($currentJoin !== null) {
                $currentJoin = $currentJoin->model->getNestedJoins('person')[0] ?? null;
                if ($currentJoin) {
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

            if ($exceedLimit > 1) {
                // NOTE: joinRecursively returns at least 1 model instance
                // as we already have one above, we now have to reduce by 2 (!)
                $evenDeeperModel = $this->joinRecursively('person', $exceedLimit - 2, true);
                $deeperModel->addModel($evenDeeperModel);
            }
            $limit += $exceedLimit;
        }


        $dataset = null;
        $savedExceeded = 0;

        // $maxI = $limit + 1;
        foreach (range($limit + 1, 1) as $i) {
            $dataset = [
              'person_firstname' => 'firstname' . $i,
              'person_lastname' => 'testJoinNestingLimitMaxxedOutSaving',
              'person_parent' => $dataset,
            ];
            if ($exceedLimit && ($i > ($limit - $exceedLimit + 1))) {
                $dataset['person_country'] = 'DE';
                $savedExceeded++;
            }
        }

        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }
        $model->saveWithChildren($dataset);

        $id = $model->lastInsertId();

        $loadedDataset = $model->load($id);

        // if we have a deeper model joined
        // (see above) we verify we have those tiny modifications
        // successfully saved
        if ($deeperModel) {
            $deeperId = $deeperModel->lastInsertId();
            $deeperDataset = $deeperModel->load($deeperId);

            // print_r($deeperDataset);
            static::assertEquals($exceedLimit, $savedExceeded);

            $diveDataset = $deeperDataset;
            for ($i = 0; $i < $savedExceeded; $i++) {
                static::assertEquals('DE', $diveDataset['person_country']);
                $diveDataset = $diveDataset['person_parent'] ?? null;
            }
        }

        static::assertEquals('firstname1', $loadedDataset['person_firstname']);

        foreach (range(0, $limit) as $l) {
            $path = array_fill(0, $l, 'person_parent');
            $childDataset = deepaccess::get($dataset, $path);
            static::assertEquals('firstname' . ($l + 1), $childDataset['person_firstname']);
        }

        $cnt = $this->getModel('person')
          ->addFilter('person_lastname', 'testJoinNestingLimitMaxxedOutSaving')
          ->getCount();
        static::assertEquals($limit + 1, $cnt);

        $personModel = $this->getModel('person')
          ->addDefaultFilter('person_lastname', 'testJoinNestingLimitMaxxedOutSaving');

        if (!($personModel instanceof sql)) {
            static::fail('setup fail');
        }

        $personModel
          ->update([
            'person_parent_id' => null,
          ])
          ->delete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingBypassLimitation1(): void
    {
        $this->testJoinNestingLimit(1);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingBypassLimitation2(): void
    {
        $this->testJoinNestingLimit(2);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testJoinNestingBypassLimitation3(): void
    {
        $this->testJoinNestingLimit(3);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGetCount(): void
    {
        $model = $this->getModel('testdata');

        static::assertEquals(4, $model->getCount());

        $model->addFilter('testdata_text', 'bar');
        static::assertEquals(2, $model->getCount());

        // Test model getCount() to _NOT_ reset filters
        static::assertEquals(2, $model->getCount());

        // Explicit reset
        $model->reset();
        static::assertEquals(4, $model->getCount());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFlags(): void
    {
        $model = $this->getModel('testdata');
        $model->save([
          'testdata_text' => 'flagtest',
        ]);
        $id = $model->lastInsertId();

        $model->entryLoad($id);

        $flags = $model->getConfig()->get('flag');
        static::assertCount(4, $flags);

        foreach ($flags as $flagName => $flagMask) {
            static::assertEquals($model->getConfig()->get("flag>$flagName"), $flagMask);
            static::assertFalse($model->isFlag($flagMask, $model->getData()));
        }

        $model->save(
            $model->normalizeData([
              $model->getPrimaryKey() => $id,
              'testdata_flag' => [
                'foo' => true,
                'baz' => true,
              ],
            ])
        );

        //
        // we should only have one.
        // see above.
        //
        $res = $model->withFlag($model->getConfig()->get('flag>foo'))->search()->getResult();
        static::assertCount(1, $res);

        // We assume the base testdata entries have a null value
        // and therefore, are not to be included in the results at all.
        $res = $model->withoutFlag($model->getConfig()->get('flag>baz'))->search()->getResult();
        static::assertCount(0, $res);

        // combined flags filters
        $res = $model
          ->withFlag($model->getConfig()->get('flag>foo'))
          ->withoutFlag($model->getConfig()->get('flag>qux'))
          ->search()->getResult();
        static::assertCount(1, $res);

        $withDefaultFlagModel = $this->getModel('testdata')->withDefaultFlag($model->getConfig()->get('flag>foo'));
        $res1 = $withDefaultFlagModel->search()->getResult();
        $res2 = $withDefaultFlagModel->search()->getResult(); // default filters are re-applied.
        static::assertCount(1, $res1);
        static::assertEquals($res1, $res2);

        $withoutDefaultFlagModel = $this->getModel('testdata')->withoutDefaultFlag($model->getConfig()->get('flag>baz'));
        $res1 = $withoutDefaultFlagModel->search()->getResult();
        $res2 = $withoutDefaultFlagModel->search()->getResult(); // default filters are re-applied.
        static::assertCount(0, $res1);
        static::assertEquals($res1, $res2);

        $model->delete($id);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFlagfieldValueNoFlagsInModel(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_MODEL_FUNCTION_FLAGFIELDVALUE_NOFLAGSINMODEL);
        $this->getModel('person')->flagfieldValue(1, []);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFlagfieldValue(): void
    {
        $model = $this->getModel('testdata');

        static::assertEquals(0, $model->flagfieldValue(0, []), 'No flags provided');
        static::assertEquals(1, $model->flagfieldValue(1, []), 'Do not change anything');
        static::assertEquals(128, $model->flagfieldValue(128, []), 'Do not change anything, nonexisting given');

        static::assertEquals(
            1 + 8,
            $model->flagfieldValue(
                1,
                [
                  8 => true,
                ]
            ),
            'Change a single flag'
        );

        static::assertEquals(
            1 + 4 + 8,
            $model->flagfieldValue(
                1 + 2 + 8,
                [
                  4 => true,
                  2 => false,
                ]
            ),
            'Change flags'
        );

        static::assertEquals(
            1,
            $model->flagfieldValue(
                1,
                [
                  128 => true,
                ]
            ),
            'Setting invalid flag does not change anything'
        );

        static::assertEquals(
            1,
            $model->flagfieldValue(
                1,
                [
                  (1 + 2) => true,
                ]
            ),
            'Setting combined flag has no effect'
        );

        static::assertEquals(
            1,
            $model->flagfieldValue(
                1,
                [
                  -2 => true,
                ]
            ),
            'Setting invalid/negative flag has no effect'
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGetFlagNonexisting(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(model::EXCEPTION_GETFLAG_FLAGNOTFOUND);
        $this->getModel('testdata')->getFlag('nonexisting');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testIsFlagNoFlagField(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ISFLAG_NOFLAGFIELD);
        $this->getModel('testdata')->isFlag(3, ['testdata_text' => 'abc']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFlagNormalization(): void
    {
        $model = $this->getModel('testdata');

        //
        // no normalization, if value provided
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => 1,
        ]);
        static::assertEquals(1, $normalized['testdata_flag']);

        //
        // retain value, if flag values present
        // that are not defined
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => 123,
        ]);
        static::assertEquals(123, $normalized['testdata_flag']);

        //
        // no flag (array-technique)
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => [],
        ]);
        static::assertEquals(0, $normalized['testdata_flag']);

        //
        // single flag
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => [
            'foo' => true,
          ],
        ]);
        static::assertEquals(1, $normalized['testdata_flag']);

        //
        // multiple flags
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => [
            'foo' => true,
            'baz' => true,
            'qux' => false,
          ],
        ]);
        static::assertEquals(5, $normalized['testdata_flag']);

        //
        // nonexisting flag
        //
        $normalized = $model->normalizeData([
          'testdata_flag' => [
            'nonexisting' => true,
            'foo' => true,
            'baz' => true,
            'qux' => false,
          ],
        ]);
        static::assertEquals(5, $normalized['testdata_flag']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddModelExplicitModelfieldValid(): void
    {
        $saveCustomerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel($savePersonModel = $this->getModel('person'));
        if (!($saveCustomerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $saveCustomerModel->saveWithChildren([
          'customer_no' => 'ammv',
          'customer_person' => [
            'person_firstname' => 'ammv1',
          ],
        ]);
        $customerId = $saveCustomerModel->lastInsertId();
        $personId = $savePersonModel->lastInsertId();


        $model = $this->getModel('customer')
          ->addModel(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_person_id'
          );

        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // TODO: detail data tests?

        $saveCustomerModel->delete($customerId);
        $savePersonModel->delete($personId);
        static::assertEmpty($savePersonModel->load($personId));
        static::assertEmpty($saveCustomerModel->load($customerId));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddModelExplicitModelfieldInvalid(): void
    {
        //
        // Try to join on a field that's not designed for it
        //
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION');

        $this->getModel('customer')
          ->addModel(
              $this->getModel('person'),
              join::TYPE_LEFT,
              'customer_no' // invalid field for this model
          );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddModelInvalidNoRelation(): void
    {
        //
        // Try to join a model that has no relation to it
        //
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_ADDMODEL_INVALID_OPERATION');

        $this->getModel('testdata')
          ->addModel(
              $this->getModel('person')
          );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testVirtualFieldResultSaving(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel(
              $personModel = $this->getModel('person')->setVirtualFieldResult(true)
                ->addModel($parentPersonModel = $this->getModel('person'))
          )
          ->addCollectionModel($this->getModel('contactentry'));

        $dataset = [
          'customer_no' => 'K1000',
          'customer_person' => [
            'person_firstname' => 'John',
            'person_lastname' => 'Doe',
            'person_birthdate' => '1970-01-01',
            'person_parent' => [
              'person_firstname' => 'Maria',
              'person_lastname' => 'Ada',
              'person_birthdate' => null,
            ],
          ],
          'customer_contactentries' => [
            ['contactentry_name' => 'Phone', 'contactentry_telephone' => '+49123123123'],
          ],
        ];

        static::assertTrue($customerModel->isValid($dataset));

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $customerModel->saveWithChildren($dataset);

        $customerId = $customerModel->lastInsertId();
        $personId = $personModel->lastInsertId();
        $parentPersonId = $parentPersonModel->lastInsertId();

        $dataset = $customerModel->load($customerId);

        static::assertEquals('K1000', $dataset['customer_no']);
        static::assertEquals('John', $dataset['customer_person']['person_firstname']);
        static::assertEquals('Doe', $dataset['customer_person']['person_lastname']);
        static::assertEquals('Phone', $dataset['customer_contactentries'][0]['contactentry_name']);
        static::assertEquals('+49123123123', $dataset['customer_contactentries'][0]['contactentry_telephone']);

        static::assertEquals('Maria', $dataset['customer_person']['person_parent']['person_firstname']);
        static::assertEquals('Ada', $dataset['customer_person']['person_parent']['person_lastname']);
        static::assertEquals(null, $dataset['customer_person']['person_parent']['person_birthdate']);

        static::assertNotNull($dataset['customer_id']);
        static::assertNotNull($dataset['customer_person']['person_id']);
        static::assertNotNull($dataset['customer_contactentries'][0]['contactentry_id']);

        static::assertEquals($dataset['customer_person_id'], $dataset['customer_person']['person_id']);
        static::assertEquals($dataset['customer_contactentries'][0]['contactentry_customer_id'], $dataset['customer_id']);

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        //
        // Cleanup
        //
        $customerModel->saveWithChildren([
          $customerModel->getPrimaryKey() => $customerId,
            // Implicitly remove contactentries by saving an empty collection (Not null!)
          'customer_contactentries' => [],
        ]);
        $customerModel->delete($customerId);
        $personModel->delete($personId);
        $parentPersonModel->delete($parentPersonId);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testVirtualFieldResultCollectionHandling(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addCollectionModel($this->getModel('contactentry'));

        $dataset = [
          'customer_no' => 'K1002',
          'customer_contactentries' => [
            ['contactentry_name' => 'Entry1', 'contactentry_telephone' => '+49123123123'],
            ['contactentry_name' => 'Entry2', 'contactentry_telephone' => '+49234234234'],
            ['contactentry_name' => 'Entry3', 'contactentry_telephone' => '+49345345345'],
          ],
        ];

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $customerModel->saveWithChildren($dataset);
        $id = $customerModel->lastInsertId();

        $customer = $customerModel->load($id);
        static::assertCount(3, $customer['customer_contactentries']);

        // delete the middle contactentry
        unset($customer['customer_contactentries'][1]);

        // store PKEYs of other entries
        $contactentryIds = array_column($customer['customer_contactentries'], 'contactentry_id');
        $customerModel->saveWithChildren($customer);

        $customerModified = $customerModel->load($id);
        static::assertCount(2, $customerModified['customer_contactentries']);

        $contactentryIdsVerify = array_column($customerModified['customer_contactentries'], 'contactentry_id');

        // assert the IDs haven't changed
        static::assertEquals($contactentryIds, $contactentryIdsVerify);

        // assert nothing happens if a null value is provided or being unset
        $customerUnsetCollection = $customerModified;
        unset($customerUnsetCollection['customer_contactentries']);
        $customerModel->saveWithChildren($customerUnsetCollection);
        static::assertEquals($customerModified['customer_contactentries'], $customerModel->load($id)['customer_contactentries']);

        $customerNullCollection = $customerModified;
        $customerNullCollection['customer_contactentries'] = null;
        $customerModel->saveWithChildren($customerNullCollection);
        static::assertEquals($customerModified['customer_contactentries'], $customerModel->load($id)['customer_contactentries']);

        //
        // Cleanup
        //
        $customerModel->saveWithChildren([
          $customerModel->getPrimaryKey() => $id,
            // Implicitly remove contactentries by saving an empty collection (Not null!)
          'customer_contactentries' => [],
        ]);
        $customerModel->delete($id);
    }

    /**
     * Tests trying ::addCollectionModel w/o having the respective config.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddCollectionModelMissingCollectionConfig(): void
    {
        // Testdata model does not have a collection config
        // (or, at least, it shouldn't have)
        $model = $this->getModel('testdata');
        static::assertFalse($model->getConfig()->exists('collection'));

        $this->expectExceptionMessage('EXCEPTION_NO_COLLECTION_KEY');
        $model->addCollectionModel($this->getModel('details'));
    }

    /**
     * Tests trying to ::addCollectionModel with an unsupported/unspecified model
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddCollectionModelIncompatible(): void
    {
        $model = $this->getModel('customer');
        $this->expectExceptionMessage('EXCEPTION_UNKNOWN_COLLECTION_MODEL');
        $model->addCollectionModel($this->getModel('person'));
    }

    /**
     * Tests trying to ::addCollectionModel with a valid collection model
     * but simply a wrong or nonexisting field
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddCollectionModelInvalidModelField(): void
    {
        $model = $this->getModel('customer');
        $this->expectExceptionMessage('EXCEPTION_NO_COLLECTION_CONFIG');
        $model->addCollectionModel(
            $this->getModel('contactentry'), // Compatible
            'nonexisting_collection_field'   // different field - or incompatible
        );
    }

    /**
     * Tests trying to ::addCollectionModel with an incompatible model
     *  but a valid/existing collection field key
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddCollectionModelValidModelFieldIncompatibleModel(): void
    {
        $model = $this->getModel('customer');
        $this->expectExceptionMessage('EXCEPTION_MODEL_ADDCOLLECTIONMODEL_INCOMPATIBLE');
        $model->addCollectionModel(
            $this->getModel('person'), // Incompatible
            'customer_contactentries'  // Existing/valid field, but irrelevant for the model to be joined
        );
    }

    /**
     * Tests various cases of collection retrieval
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGetNestedCollections(): void
    {
        // Model w/o any collection config
        static::assertEmpty($this->getModel('testdata')->getNestedCollections());

        // Model with available, but unused collection
        static::assertEmpty(
            $this->getModel('customer')
              ->getNestedCollections()
        );

        // Model with available and _used_ collection
        $collections = $this->getModel('customer')
          ->addCollectionModel($this->getModel('contactentry'))
          ->getNestedCollections();

        static::assertNotEmpty($collections);
        static::assertCount(1, $collections);

        $collectionPlugin = $collections['customer_contactentries'];
        static::assertInstanceOf(collection::class, $collectionPlugin);

        static::assertEquals('customer', $collectionPlugin->baseModel->getIdentifier());
        static::assertEquals('customer_id', $collectionPlugin->getBaseField());
        static::assertEquals('customer_contactentries', $collectionPlugin->field->get());
        static::assertEquals('contactentry', $collectionPlugin->collectionModel->getIdentifier());
        static::assertEquals('contactentry_customer_id', $collectionPlugin->getCollectionModelBaseRefField());
    }

    /**
     * test saving (expect a crash) when having two models joined ambiguously
     * in virtual field result mode
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testVirtualFieldResultSavingFailedAmbiguousJoins(): void
    {
        $customerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel($this->getModel('person'))
          ->addModel($this->getModel('person')) // double joined
          ->addCollectionModel($this->getModel('contactentry'));

        $dataset = [
          'customer_no' => 'K1001',
          'customer_person' => [
            'person_firstname' => 'John',
            'person_lastname' => 'Doe',
            'person_birthdate' => '1970-01-01',
          ],
          'customer_contactentries' => [
            ['contactentry_name' => 'Phone', 'contactentry_telephone' => '+49123123123'],
          ],
        ];

        static::assertTrue($customerModel->isValid($dataset));

        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_CHILDREN_AMBIGUOUS_JOINS');

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $customerModel->saveWithChildren($dataset);
        // No need to clean up, as it must fail beforehand
    }

    /**
     * tests a runtime-based virtual field
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testVirtualFieldQuery(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true);
        $model->addVirtualField('virtual_field', function ($dataset) {
            return $dataset['testdata_id'];
        });
        $res = $model->search()->getResult();

        static::assertCount(4, $res);
        foreach ($res as $r) {
            static::assertEquals($r['testdata_id'], $r['virtual_field']);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testForcedVirtualJoinWithVirtualFieldResult(): void
    {
        $this->testForcedVirtualJoin(true);
    }

    /**
     * @param bool $virtualFieldResult [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testForcedVirtualJoin(bool $virtualFieldResult): void
    {
        //
        // Store test data
        //
        $saveCustomerModel = $this->getModel('customer')->setVirtualFieldResult(true)
          ->addModel($savePersonModel = $this->getModel('person')->setVirtualFieldResult(true));
        if (!($saveCustomerModel instanceof sql)) {
            static::fail('setup fail');
        }
        $saveCustomerModel->saveWithChildren([
          'customer_no' => 'fvj',
          'customer_person' => [
            'person_firstname' => 'forced',
            'person_lastname' => 'virtualjoin',
          ],
        ]);
        $customerId = $saveCustomerModel->lastInsertId();
        $personId = $savePersonModel->lastInsertId();

        $referenceCustomerModel = $this->getModel('customer')->setVirtualFieldResult($virtualFieldResult)
          ->addModel($this->getModel('person')->setVirtualFieldResult($virtualFieldResult));

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
        static::assertNotNull($customerLastQuery);
        static::assertNotNull($personLastQuery);
        static::assertNotEquals($customerLastQuery, $personLastQuery);

        foreach ($referenceDataset as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($v !== null) {
                        static::assertEquals($v, $compareDataset[$key][$k]);
                    }
                }
            } elseif ($value !== null) {
                static::assertEquals($value, $compareDataset[$key]);
            }
        }

        // Assert both datasets are equal
        // static::assertEquals($referenceDataset, $compareDataset);
        // NOTE: doesn't work right now, because:
        // $this->addWarning('Some bug when doing forced virtual joins and unjoined vfields exist');
        // NOTE/CHANGED 2021-04-13: fixed.

        // make sure to clean up
        $saveCustomerModel->delete($customerId);
        $savePersonModel->delete($personId);
        static::assertEmpty($saveCustomerModel->load($customerId));
        static::assertEmpty($savePersonModel->load($personId));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testForcedVirtualJoinWithoutVirtualFieldResult(): void
    {
        $this->testForcedVirtualJoin(false);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testModelJoinWithJson(): void
    {
        // inject some base data, first
        $model = $this->getModel('person')
          ->addModel($this->getModel('country'));

        $model->save([
          'person_firstname' => 'German',
          'person_lastname' => 'Resident',
          'person_country' => 'DE',
        ]);
        $id = $model->lastInsertId();

        $res = $model->load($id);
        static::assertEquals('DE', $res['person_country']);
        static::assertEquals('DE', $res['country_code']);
        static::assertEquals('Germany', $res['country_name']);

        $model->delete($id);
        static::assertEmpty($model->load($id));

        //
        // save another one, but without FKEY value for country
        //
        $model->save([
          'person_firstname' => 'Resident',
          'person_lastname' => 'Without Country',
          'person_country' => null,
        ]);
        $id = $model->lastInsertId();

        $res = $model->load($id);
        static::assertEquals(null, $res['person_country']);
        static::assertEquals(null, $res['country_code']);
        static::assertEquals(null, $res['country_name']);

        $model->delete($id);
        static::assertEmpty($model->load($id));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInvalidFilterOperator(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_INVALID_OPERATOR');
        $model = $this->getModel('testdata');
        $model->addFilter('testdata_integer', 42, '%&/');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testLikeFilters(): void
    {
        $model = $this->getModel('testdata');

        // NOTE: this is case-sensitive on PG
        $res = $model
          ->addFilter('testdata_text', 'F%', 'LIKE')
          ->search()->getResult();
        static::assertCount(2, $res);

        // NOTE: this is case-sensitive on PG
        $res = $model
          ->addFilter('testdata_text', 'f%', 'LIKE')
          ->search()->getResult();
        static::assertCount(2, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testSuccessfulCreateAndDeleteTransaction(): void
    {
        $testTransactionModel = $this->getModel('testdata');

        $transaction = new transaction('test', [$testTransactionModel]);
        $transaction->start();

        // insert a new entry
        $testTransactionModel->save([
          'testdata_integer' => 999,
        ]);
        $id = $testTransactionModel->lastInsertId();

        // load the new dataset in the transaction
        $newDataset = $testTransactionModel->load($id);
        static::assertEquals(999, $newDataset['testdata_integer']);

        // delete it
        $testTransactionModel->delete($id);

        // end transaction, as if nothing happened
        $transaction->end();

        // Make sure it hasn't changed
        static::assertEquals(4, $testTransactionModel->getCount());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testTransactionUntrackedRunning(): void
    {
        $model = $this->getModel('testdata');
        if ($model instanceof sql) {
            // Make sure there's no open transaction
            // and start an untracked, new one.
            static::assertFalse($model->getConnection()->getConnection()->inTransaction());
            static::assertTrue($model->getConnection()->getConnection()->beginTransaction());

            $this->expectExceptionMessage('EXCEPTION_DATABASE_VIRTUALTRANSACTION_UNTRACKED_TRANSACTION_RUNNING');

            $transaction = new transaction('untracked_transaction_test', [$model]);
            $transaction->start();
        } else {
            static::markTestSkipped('Not applicable.');
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testTransactionRolledBackPrematurely(): void
    {
        $model = $this->getModel('testdata');
        if ($model instanceof sql) {
            // Make sure there's no open transaction
            static::assertFalse($model->getConnection()->getConnection()->inTransaction());

            $this->expectExceptionMessage('EXCEPTION_DATABASE_VIRTUALTRANSACTION_UNTRACKED_TRANSACTION_RUNNING');

            $transaction = new transaction('untracked_transaction_test', [$model]);
            $transaction->start();

            // Make sure transaction has begun
            static::assertTrue($model->getConnection()->getConnection()->inTransaction());

            // End transaction/rollback right away
            static::assertTrue($model->getConnection()->getConnection()->rollBack());

            // try to end transaction normally. But it was canceled before
            $this->expectExceptionMessage('EXCEPTION_DATABASE_VIRTUALTRANSACTION_END_TRANSACTION_INTERRUPTED');
            $transaction->end();
        } else {
            static::markTestSkipped('Not applicable.');
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testNestedOrder(): void
    {
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
              'person_lastname' => 'Anderson',
              'person_birthdate' => '1978-02-03',
            ],
          ],
          [
            'customer_no' => 'A1001',
            'customer_person' => [
              'person_firstname' => 'Bridget',
              'person_lastname' => 'Balmer',
              'person_birthdate' => '1981-11-15',
            ],
          ],
          [
            'customer_no' => 'A1002',
            'customer_person' => [
              'person_firstname' => 'Christian',
              'person_lastname' => 'Crossback',
              'person_birthdate' => '1990-04-19',
            ],
          ],
          [
            'customer_no' => 'A1003',
            'customer_person' => [
              'person_firstname' => 'Dodgy',
              'person_lastname' => 'Data',
              'person_birthdate' => null,
            ],
          ],
        ];

        if (!($customerModel instanceof sql)) {
            static::fail('setup fail');
        }

        foreach ($datasets as $d) {
            $customerModel->saveWithChildren($d);
            $customerIds[] = $customerModel->lastInsertId();
            $personIds[] = $personModel->lastInsertId();
        }

        $customerModel->addOrder('person.person_birthdate', 'DESC');
        $res = $customerModel->search()->getResult();

        static::assertEquals(['A1002', 'A1001', 'A1000', 'A1003'], array_column($res, 'customer_no'));
        static::assertEquals(
            ['Christian', 'Bridget', 'Alex', 'Dodgy'],
            array_map(function ($dataset) {
                return $dataset['customer_person']['person_firstname'];
            }, $res)
        );

        // cleanup
        foreach ($customerIds as $id) {
            $customerModel->delete($id);
        }
        foreach ($personIds as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testOrderLimitOffset(): void
    {
        // Generic model features
        // Offset [& Limit & Order]
        $testLimitModel = $this->getModel('testdata');
        $testLimitModel->addOrder('testdata_id');
        $testLimitModel->setLimit(1);
        $testLimitModel->setOffset(1);
        $res = $testLimitModel->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals('bar', $res[0]['testdata_text']);
        static::assertEquals(4.25, $res[0]['testdata_number']);
    }

    /**
     * Tests setting limit & offset twice (reset)
     * as only ONE limit and offset is allowed at a time
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testLimitOffsetReset(): void
    {
        $model = $this->getModel('testdata');
        $model->addOrder('testdata_id');
        $model->setLimit(1);
        $model->setOffset(1);
        $model->setLimit(0);
        $model->setOffset(0);
        $res = $model->search()->getResult();
        static::assertCount(4, $res);
    }

    /**
     * Tests whether calling model::addOrder() using a nonexisting field
     * throws an exception
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddOrderOnNonexistingFieldWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(model::EXCEPTION_ADDORDER_FIELDNOTFOUND);
        $model = $this->getModel('testdata');
        $model->addOrder('testdata_nonexisting');
    }

    /**
     * Tests updating a structure field (simple)
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testStructureData(): void
    {
        $model = $this->getModel('testdata');
        $res = $model
          ->addFilter('testdata_text', 'foo')
          ->addFilter('testdata_date', '2021-03-22')
          ->addFilter('testdata_number', 3.14)
          ->search()->getResult();
        static::assertCount(1, $res);

        $testdata = $res[0];
        $id = $testdata[$model->getPrimaryKey()];

        $model->save([
          $model->getPrimaryKey() => $testdata[$model->getPrimaryKey()],
          'testdata_structure' => ['changed' => true],
        ]);
        $updated = $model->load($id);
        static::assertEquals(['changed' => true], $updated['testdata_structure']);
        $model->save($testdata);
        $restored = $model->load($id);
        static::assertEquals($testdata['testdata_structure'], $restored['testdata_structure']);
    }

    /**
     * tests internal handling during saving (create & update)
     * and mass updates that might encode given object/array data
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testStructureEncoding(): void
    {
        $model = $this->getModel('testdata');

        $model->save([
          'testdata_text' => 'structure-object',
          'testdata_structure' => $testObjectData = [
            'umlautÄÜÖäüöß' => '"and quotes"',
            'and\\backlashes' => '\\some\\\"backslashes',
            'and some more' => 'with nul bytes' . chr(0),
            "with special bytes \u00c2\u00ae" => "\xc3\xa9",
          ],
        ]);
        $objectDataId = $model->lastInsertId();

        $model->save([
          'testdata_text' => 'structure-array',
          'testdata_structure' => $testArrayData = [
            'umlautÄÜÖäüöß',
            '"and quotes"',
            'and\\backlashes',
            '\\some\\\"backslashes',
            'and some more',
            "with special bytes \u00c2\u00ae",
            "\xc3\xa9",
            'with nul bytes' . chr(0),
            'more data',
          ],
        ]);
        $arrayDataId = $model->lastInsertId();

        $storedObjectData = $model->load($objectDataId)['testdata_structure'];
        $storedArrayData = $model->load($arrayDataId)['testdata_structure'];

        static::assertEquals($testObjectData, $storedObjectData);
        static::assertEquals($testArrayData, $storedArrayData);

        $model->save([
          $model->getPrimaryKey() => $objectDataId,
          'testdata_structure' => $updatedObjectData = array_merge(
              $storedObjectData,
              [
                'updated' => 1,
              ]
          ),
        ]);

        $model->save([
          $model->getPrimaryKey() => $arrayDataId,
          'testdata_structure' => $updatedArrayData = array_merge(
              $storedArrayData,
              [
                'updated',
              ]
          ),
        ]);

        $storedObjectData = $model->load($objectDataId)['testdata_structure'];
        $storedArrayData = $model->load($arrayDataId)['testdata_structure'];

        static::assertEquals($updatedObjectData, $storedObjectData);
        static::assertEquals($updatedArrayData, $storedArrayData);

        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }

        $model->addFilter($model->getPrimaryKey(), $objectDataId);
        $model->update([
          'testdata_structure' => $updatedObjectData = array_merge(
              $storedObjectData,
              [
                'updated' => 2,
              ]
          ),
        ]);

        $model->addFilter($model->getPrimaryKey(), $arrayDataId);
        $model->update([
          'testdata_structure' => $updatedArrayData = array_merge(
              $storedArrayData,
              [
                'updated',
              ]
          ),
        ]);

        $storedObjectData = $model->load($objectDataId)['testdata_structure'];
        $storedArrayData = $model->load($arrayDataId)['testdata_structure'];

        static::assertEquals($updatedObjectData, $storedObjectData);
        static::assertEquals($updatedArrayData, $storedArrayData);

        $model->delete($objectDataId);
        $model->delete($arrayDataId);
    }

    /**
     * tests model::getCount() when having a grouped query
     * should return the final count of results
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGroupedGetCount(): void
    {
        $model = $this->getModel('testdata');
        $model->addGroup('testdata_text');
        static::assertEquals(2, $model->getCount());
    }

    /**
     * Tests correct aliasing when using the same model twice
     * and calling ->getCount()
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGetCountAliasing(): void
    {
        $model = $this->getModel('person')
          ->addModel($this->getModel('person'));

        static::assertEquals(0, $model->getCount());
    }

    /**
     * Tests grouping on a calculated field
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddGroupOnCalculatedFieldDoesNotCrash(): void
    {
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
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddGroupOnNestedCalculatedFieldDoesNotCrash(): void
    {
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
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddGroupNonExistingField(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(model::EXCEPTION_ADDGROUP_FIELDDOESNOTEXIST);

        $model = $this->getModel('testdata')
          ->addModel($this->getModel('details'));

        $model->addGroup('nonexisting');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAmbiguousAliasFieldsNormalization(): void
    {
        $model = $this->getModel('testdata')
          ->addField('testdata_text', 'aliasedField')
          ->addModel(
              $this->getModel('details')
                ->addField('details_data', 'aliasedField')
          );

        $res = $model->search()->getResult();

        // Same-level keys mapped to array
        static::assertEquals([
          ['foo', null],
          ['bar', null],
          ['foo', null],
          ['bar', null],
        ], array_column($res, 'aliasedField'));

        // Modify model to put details into a virtual field
        $model->setVirtualFieldResult(true);
        $model->getNestedJoins('details')[0]->virtualField = 'temp_virtual';

        $res2 = $model->search()->getResult();

        static::assertEquals(['foo', 'bar', 'foo', 'bar'], array_column($res2, 'aliasedField'));
        static::assertEquals([null, null, null, null], array_column(array_column($res2, 'temp_virtual'), 'aliasedField'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateCount(): void
    {
        //
        // Aggregate: count plugin
        //
        $testCountModel = $this->getModel('testdata');
        $testCountModel->addAggregateField('entries_count', 'count', 'testdata_id');

        // count w/o filters
        static::assertEquals(4, $testCountModel->search()->getResult()[0]['entries_count']);

        // w/ simple filter added
        $testCountModel->addFilter('testdata_datetime', '2020-01-01', '>=');
        static::assertEquals(3, $testCountModel->search()->getResult()[0]['entries_count']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateCountDistinct(): void
    {
        //
        // Aggregate: count_distinct plugin
        //
        $testCountDistinctModel = $this->getModel('testdata');
        $testCountDistinctModel->addAggregateField('entries_count', 'count_distinct', 'testdata_text');

        // count w/o filters
        static::assertEquals(2, $testCountDistinctModel->search()->getResult()[0]['entries_count']);

        // w/ simple filter added - we only expect a count of 1
        $testCountDistinctModel
          ->addFilter('testdata_datetime', '2021-03-23', '>=');
        static::assertEquals(1, $testCountDistinctModel->search()->getResult()[0]['entries_count']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddAggregateFieldDuplicateFixedFieldWillThrow(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ADDAGGREGATEFIELD_FIELDALREADYEXISTS);
        $model = $this->getModel('testdata');
        // Try to add the aggregate field as a field that already exists
        // as a defined model field - in this case, simply use the PKEY...
        $model->addAggregateField('testdata_id', 'count_distinct', 'testdata_text');
    }

    /**
     * Tests a rare edge case
     * of using an aggregate field with the same name
     * as a field of a nested model with enabled VFR
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddAggregateFieldSameNamedWithVirtualFieldResult(): void
    {
        $model = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addModel($this->getModel('details'));

        $model->getNestedJoins('details')[0]->virtualField = 'details';
        // Try to add the aggregate field as a field that already exists
        // in a _nested_ mode as a defined model field - in this case, simply use the PKEY...
        $model->addAggregateField('details_id', 'count_distinct', 'testdata_text');

        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals(2, $res[0]['details_id']); // this really is the aggregate field...
        static::assertEquals(null, $res[0]['details']['details_id']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateSum(): void
    {
        //
        // Aggregate: sum plugin
        //
        $testSumModel = $this->getModel('testdata');
        $testSumModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');

        // count w/o filters
        static::assertEquals(48, $testSumModel->search()->getResult()[0]['entries_sum']);

        // w/ simple filter added
        $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
        static::assertEquals(6, $testSumModel->search()->getResult()[0]['entries_sum']);

        // no entries matching filter
        $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
        static::assertEquals(0, $testSumModel->search()->getResult()[0]['entries_sum']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateAvg(): void
    {
        //
        // Aggregate: avg plugin
        //
        $testSumModel = $this->getModel('testdata');
        $testSumModel->addAggregateField('entries_avg', 'avg', 'testdata_number');

        // count w/o filters
        static::assertEquals((3.14 + 4.25 + 5.36 + 0.99) / 4, $testSumModel->search()->getResult()[0]['entries_avg']);

        // w/ simple filter added
        $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
        static::assertEquals((3.14 + 4.25 + 5.36) / 3, $testSumModel->search()->getResult()[0]['entries_avg']);

        // no entries matching filter
        $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
        static::assertEquals(0, $testSumModel->search()->getResult()[0]['entries_avg']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateMax(): void
    {
        //
        // Aggregate: max plugin
        //
        $model = $this->getModel('testdata');
        $model->addAggregateField('entries_max', 'max', 'testdata_number');

        // count w/o filters
        static::assertEquals(5.36, $model->search()->getResult()[0]['entries_max']);

        // w/ simple filter added
        $model->addFilter('testdata_datetime', '2021-03-22', '>=');
        static::assertEquals(5.36, $model->search()->getResult()[0]['entries_max']);

        // w/ simple filter added
        $model->addFilter('testdata_datetime', '2021-03-22 23:59:59', '<=');
        static::assertEquals(4.25, $model->search()->getResult()[0]['entries_max']);

        // no entries matching filter
        $model->addFilter('testdata_datetime', '2019-01-01', '<=');
        static::assertEquals(0, $model->search()->getResult()[0]['entries_max']);

        // w/ added grouping
        $model->addGroup('testdata_date');
        $model->addOrder('testdata_date');
        // max per day
        static::assertEquals([0.99, 4.25, 5.36], array_column($model->search()->getResult(), 'entries_max'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateMin(): void
    {
        //
        // Aggregate: min plugin
        //
        $model = $this->getModel('testdata');
        $model->addAggregateField('entries_min', 'min', 'testdata_number');

        // count w/o filters
        static::assertEquals(0.99, $model->search()->getResult()[0]['entries_min']);

        // w/ simple filter added
        $model->addFilter('testdata_datetime', '2021-03-22', '>=');
        static::assertEquals(3.14, $model->search()->getResult()[0]['entries_min']);

        // w/ simple filter added
        $model->addFilter('testdata_datetime', '2021-03-22 23:59:59', '<=');
        static::assertEquals(0.99, $model->search()->getResult()[0]['entries_min']);

        // no entries matching filter
        $model->addFilter('testdata_datetime', '2019-01-01', '<=');
        static::assertEquals(0, $model->search()->getResult()[0]['entries_min']);

        // w/ added grouping
        $model->addGroup('testdata_date');
        $model->addOrder('testdata_date');
        // min per day
        static::assertEquals([0.99, 3.14, 5.36], array_column($model->search()->getResult(), 'entries_min'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateDatetimeYear(): void
    {
        //
        // Aggregate DateTime plugin
        //
        $testYearModel = $this->getModel('testdata');
        $testYearModel->addAggregateField('entries_year1', 'year', 'testdata_datetime');
        $testYearModel->addAggregateField('entries_year2', 'year', 'testdata_date');
        $testYearModel->addOrder('testdata_id');
        $yearRes = $testYearModel->search()->getResult();
        static::assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year1'));
        static::assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateGroupedSumOrderByAggregateField(): void
    {
        $testYearModel = $this->getModel('testdata');
        $testYearModel->addAggregateField('entries_year1', 'year', 'testdata_datetime');
        $testYearModel->addAggregateField('entries_year2', 'year', 'testdata_date');
        // add a grouping modifier (WARNING, instance modified)
        // introduce additional summing
        // and order by calculated/aggregate field
        $testYearModel->addGroup('entries_year1');
        $testYearModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');
        $testYearModel->addOrder('entries_year1');
        $yearGroupedRes = $testYearModel->search()->getResult();

        static::assertEquals(2019, $yearGroupedRes[0]['entries_year1']);
        static::assertEquals(42, $yearGroupedRes[0]['entries_sum']);
        static::assertEquals(2021, $yearGroupedRes[1]['entries_year1']);
        static::assertEquals(6, $yearGroupedRes[1]['entries_sum']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateDatetimeInvalid(): void
    {
        //
        // Tests an invalid type config for Aggregate DateTime plugin
        //
        $this->expectException(exception::class);
        $model = $this->getModel('testdata');
        $model->addAggregateField('entries_invalid1', 'invalid', 'testdata_datetime');
        $model->search()->getResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateDatetimeQuarter(): void
    {
        //
        // Aggregate Quarter plugin
        //
        $testQuarterModel = $this->getModel('testdata');
        $testQuarterModel->addAggregateField('entries_quarter1', 'quarter', 'testdata_datetime');
        $testQuarterModel->addAggregateField('entries_quarter2', 'quarter', 'testdata_date');
        $testQuarterModel->addOrder('testdata_id');
        $res = $testQuarterModel->search()->getResult();
        static::assertEquals([1, 1, 1, 1], array_column($res, 'entries_quarter1'));
        static::assertEquals([1, 1, 1, 1], array_column($res, 'entries_quarter2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateDatetimeMonth(): void
    {
        //
        // Aggregate DateTime plugin
        //
        $testMonthModel = $this->getModel('testdata');
        $testMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
        $testMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
        $testMonthModel->addOrder('testdata_id');
        $res = $testMonthModel->search()->getResult();
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateDatetimeDay(): void
    {
        //
        // Aggregate DateTime plugin
        //
        $model = $this->getModel('testdata');
        $model->addAggregateField('entries_day1', 'day', 'testdata_datetime');
        $model->addAggregateField('entries_day2', 'day', 'testdata_date');
        $model->addOrder('testdata_id');
        $res = $model->search()->getResult();
        static::assertEquals([22, 22, 23, 01], array_column($res, 'entries_day1'));
        static::assertEquals([22, 22, 23, 01], array_column($res, 'entries_day2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateFilterSimple(): void
    {
        // Aggregate Filter
        $testAggregateFilterMonthModel = $this->getModel('testdata');

        $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
        $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
        $testAggregateFilterMonthModel->addAggregateFilter('entries_month1', 3, '>=');
        $testAggregateFilterMonthModel->addAggregateFilter('entries_month2', 3, '>=');

        // WARNING: sqlite doesn't support HAVING without GROUP BY
        $testAggregateFilterMonthModel->addGroup('testdata_id');

        $res = $testAggregateFilterMonthModel->search()->getResult();
        static::assertEquals([3, 3, 3], array_column($res, 'entries_month1'));
        static::assertEquals([3, 3, 3], array_column($res, 'entries_month2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateFilterValueArray(): void
    {
        // Aggregate Filter
        $testAggregateFilterMonthModel = $this->getModel('testdata');

        $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
        $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
        $testAggregateFilterMonthModel->addAggregateFilter('entries_month1', [1, 3]);
        $testAggregateFilterMonthModel->addAggregateFilter('entries_month2', [1, 3]);

        // WARNING: sqlite doesn't support HAVING without GROUP BY
        $testAggregateFilterMonthModel->addGroup('testdata_id');

        $res = $testAggregateFilterMonthModel->search()->getResult();
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDefaultAggregateFilterValueArray(): void
    {
        // Aggregate Filter
        $testAggregateFilterMonthModel = $this->getModel('testdata');

        $testAggregateFilterMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
        $testAggregateFilterMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
        $testAggregateFilterMonthModel->addDefaultAggregateFilter('entries_month1', [1, 3]);
        $testAggregateFilterMonthModel->addDefaultAggregateFilter('entries_month2', [1, 3]);

        // WARNING: sqlite doesn't support HAVING without GROUP BY
        $testAggregateFilterMonthModel->addGroup('testdata_id');

        $res = $testAggregateFilterMonthModel->search()->getResult();
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
        static::assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));

        // make sure the second query returns the same result
        $res2 = $testAggregateFilterMonthModel->search()->getResult();
        static::assertEquals($res, $res2);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAggregateFilterValueArraySimple(): void
    {
        // Aggregate Filter
        $model = $this->getModel('testdata');

        // Actually, there's no real aggregate field for this test
        // Instead, we're just alias existing fields.
        $model->addField('testdata_boolean', 'boolean_aliased');
        $model->addField('testdata_integer', 'integer_aliased');
        $model->addField('testdata_number', 'number_aliased');

        // WARNING: sqlite doesn't support HAVING without GROUP BY
        $model->addGroup('testdata_id');

        $model->saveLastQuery = true;

        static::assertEquals([3.14, 4.25, 5.36, 0.99], array_column($model->search()->getResult(), 'testdata_number'));

        //
        // compacted serial tests
        //
        $filterTests = [
            //
            // Datatype estimation for booleans
            //
          [
            'field' => 'boolean_aliased',
            'value' => [true],
            'expected' => [3.14, 4.25],
          ],
          [
            'field' => 'boolean_aliased',
            'value' => [true, false],
            'expected' => [3.14, 4.25, 5.36, 0.99],
          ],
          [
            'field' => 'boolean_aliased',
            'value' => [false],
            'expected' => [5.36, 0.99],
          ],

            //
            // Datatype estimation for integers
            //
          [
            'field' => 'integer_aliased',
            'value' => [1],
            'expected' => [5.36],
          ],
          [
            'field' => 'integer_aliased',
            'value' => [1, 2, 3, 42],
            'expected' => [3.14, 4.25, 5.36, 0.99],
          ],
          [
            'field' => 'integer_aliased',
            'value' => [3, 42],
            'expected' => [3.14, 0.99],
          ],

            //
            // Datatype estimation for numbers (floats, doubles, decimals)
            //
          [
            'field' => 'number_aliased',
            'value' => [5.36],
            'expected' => [5.36],
          ],
          [
            'field' => 'number_aliased',
            'value' => [3.14, 4.25, 5.36, 0.99],
            'expected' => [3.14, 4.25, 5.36, 0.99],
          ],
          [
            'field' => 'number_aliased',
            'value' => [3.14, 0.99],
            'expected' => [3.14, 0.99],
          ],
        ];

        foreach ($filterTests as $f) {
            // use aggregate filter
            $model->addAggregateFilter($f['field'], $f['value']);
            static::assertEquals($f['expected'], array_column($model->search()->getResult(), 'testdata_number'));

            // the same, but using FCs - NOTE: does not exist yet (model::aggregateFiltercollection)
            // this only works for SQLite due to its nature.
            // $model->addFilterCollection([[ 'field' => $f['field'], 'operator' => '=', 'value' => $f['value'] ]]);
            // static::assertEquals($f['expected'], array_column($model->search()->getResult(), 'testdata_number'));
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldAliasWithFilter(): void
    {
        static::markTestIncomplete('Aliased filter implementation on differing platforms is still unclear');
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
          ->addAggregateFilter('aliased_text', 'foo')
          ->search()->getResult();

        static::assertCount(1, $res);
        static::assertEquals(['aliased_text' => 'foo'], $res[0]);
    }

    /**
     * Tests the internal datatype fallback
     * executed internally when passing an array as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldAliasWithFilterArrayFallbackDataTypeSuccessful(): void
    {
        $model = $this->getModel('testdata');
        $res = $model
          ->hideAllFields()
          ->addField('testdata_text', 'aliased_text')
          ->addFilter('testdata_integer', 3)
          ->addAggregateFilter('aliased_text', ['foo'])
          ->addGroup('testdata_id') // required due to technical limitations in some RDBMS
          ->search()->getResult();

        static::assertCount(1, $res);
        static::assertEquals(['aliased_text' => 'foo'], $res[0]);
    }

    /**
     * Try to pass an unsupported value in filter value array
     * that is not covered by model::getFallbackDatatype()
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldAliasWithFilterArrayFallbackDataTypeFailsUnsupportedData(): void
    {
        $this->expectExceptionMessage('INVALID_FALLBACK_PARAMETER_TYPE');
        $model = $this->getModel('testdata');
        $model
          ->hideAllFields()
          ->addField('testdata_text', 'aliased_text')
          ->addFilter('testdata_integer', 3)
          ->addAggregateFilter('aliased_text', [new stdClass()]) // this must cause an exception
          ->addGroup('testdata_id') // required due to technical limitations in some RDBMS
          ->search()->getResult();
    }

    /**
     * Tests ->addFilter() with an empty array value as to-be-filtered-for value
     * This is an edge case which might change in the future.
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFilterWithEmptyArrayValue(): void
    {
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
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);
        static::assertEquals(4, $model->getCount());
    }

    /**
     * see above
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFiltercollectionWithEmptyArrayValue(): void
    {
        $model = $this->getModel('testdata');

        // NOTE: we have to override the error handler for a short period of time
        set_error_handler(null, E_USER_NOTICE);

        //
        // WARNING: to avoid any issue with error handlers
        // we try to keep the amount of calls not covered by the generic handler
        // at a minimum
        //
        try {
            @$model->addFilterCollection([
              ['field' => 'testdata_text', 'operator' => '=', 'value' => []],
            ]); // this is discarded internally/has no effect
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);
        static::assertEquals(4, $model->getCount());
    }

    /**
     * see above
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddDefaultfilterWithEmptyArrayValue(): void
    {
        $model = $this->getModel('testdata');

        // NOTE: we have to override the error handler for a short period of time
        set_error_handler(null, E_USER_NOTICE);

        //
        // WARNING: to avoid any issue with error handlers
        // we try to keep the amount of calls not covered by the generic handler
        // at a minimum
        //
        try {
            @$model->addDefaultFilter('testdata_text', []); // this is discarded internally/has no effect
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);
        static::assertEquals(4, $model->getCount());
    }

    /**
     * see above
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddDefaultFiltercollectionWithEmptyArrayValue(): void
    {
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
              ['field' => 'testdata_text', 'operator' => '=', 'value' => []],
            ]); // this is discarded internally/has no effect
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);
        static::assertEquals(4, $model->getCount());
    }

    /**
     * Tests ->addAggregateFilter() with an empty array value as to-be-filtered-for value
     * This is an edge case which might change in the future.
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddAggregateFilterWithEmptyArrayValue(): void
    {
        $model = $this->getModel('testdata');

        // NOTE: we have to override the error handler for a short period of time
        set_error_handler(null, E_USER_NOTICE);

        //
        // WARNING: to avoid any issue with error handlers
        // we try to keep the amount of calls not covered by the generic handler
        // at a minimum
        //
        try {
            @$model->addAggregateFilter('nonexisting', []); // this is discarded internally/has no effect
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);

        //
        // NOTE: we just test if the notice has been triggered
        // as we're not using a field that's really available
        //
    }

    /**
     * Tests ->addDefaultAggregateFilter() with an empty array value as to-be-filtered-for value
     * This is an edge case which might change in the future.
     * CHANGED 2021-09-13: we now trigger an E_USER_NOTICE when an empty array ([]) is provided as filter value
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddDefaultAggregateFilterWithEmptyArrayValue(): void
    {
        $model = $this->getModel('testdata');

        // NOTE: we have to override the error handler for a short period of time
        set_error_handler(null, E_USER_NOTICE);

        //
        // WARNING: to avoid any issue with error handlers
        // we try to keep the amount of calls not covered by the generic handler
        // at a minimum
        //
        try {
            @$model->addDefaultAggregateFilter('nonexisting', []); // this is discarded internally/has no effect
        } catch (Throwable) {
        }

        restore_error_handler();

        static::assertEquals('Empty array filter values have no effect on resultset', error_get_last()['message']);

        //
        // NOTE: we just test if the notice has been triggered
        // as we're not using a field that's really available
        //
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddDefaultfilterWithArrayValue(): void
    {
        $model = $this->getModel('testdata');
        $model->addDefaultFilter('testdata_date', ['2021-03-22', '2021-03-23']);
        static::assertCount(3, $model->search()->getResult());

        // second call, filter should still be active
        static::assertCount(3, $model->search()->getResult());

        // third call, filter should still be active
        // we reset explicitly
        $model->reset();
        static::assertCount(3, $model->search()->getResult());
    }

    /**
     * test filter with fully qualified field name
     * of _nested_ model's field on root level
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFilterRootLevelNested(): void
    {
        $model = $this->getModel('testdata')
          ->addModel($this->getModel('details'));
        $model->addFilter('testschema.details.details_id');
        $res = $model->search()->getResult();
        static::assertCount(4, $res);

        $model->addFilter('testschema.details.details_id', 1, '>');
        $res = $model->search()->getResult();
        static::assertCount(0, $res);
    }

    /**
     * test filtercollection with fully qualified field name
     * of _nested_ model's field on root level
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFiltercollectionRootLevelNested(): void
    {
        $model = $this->getModel('testdata')
          ->addModel($this->getModel('details'));
        $model->addFilterCollection([
          ['field' => 'testschema.details.details_id', 'operator' => '=', 'value' => null],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(4, $res);

        $model->addFilterCollection([
          ['field' => 'testschema.details.details_id', 'operator' => '>', 'value' => 1],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(0, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldFilter(): void
    {
        $model = $this->getModel('testdata');

        $model->addFieldFilter('testdata_integer', 'testdata_number', '<');
        $res = $model->search()->getResult();
        static::assertCount(3, $res);
        static::assertEquals([3, 2, 1], array_column($res, 'testdata_integer'));

        // vice-versa
        $model->addFieldFilter('testdata_integer', 'testdata_number', '>');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals([42], array_column($res, 'testdata_integer'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldFilterNested(): void
    {
        $model = $this->getModel('person')->setVirtualFieldResult(true);
        $model->addModel($innerModel = $this->getModel('person'));

        if (!($model instanceof sql)) {
            static::fail('setup fail');
        }

        $ids = [];

        $model->saveWithChildren([
          'person_firstname' => 'A',
          'person_lastname' => 'A',
          'person_parent' => [
            'person_firstname' => 'C',
            'person_lastname' => 'C',
          ],
        ]);

        // NOTE: take care of order!
        $ids[] = $model->lastInsertId();
        $ids[] = $innerModel->lastInsertId();

        $model->saveWithChildren([
          'person_firstname' => 'B',
          'person_lastname' => 'B',
          'person_parent' => [
            'person_firstname' => 'X',
            'person_lastname' => 'Y',
          ],
        ]);

        // NOTE: take care of order!
        $ids[] = $model->lastInsertId();
        $ids[] = $innerModel->lastInsertId();

        // should be three: A, B, C
        $res = $model->addFieldFilter('person_firstname', 'person_lastname')->search()->getResult();
        static::assertCount(3, $res);
        static::assertEqualsCanonicalizing(['A', 'B', 'C'], array_column($res, 'person_lastname'));

        // should be one: X/Y
        $res = $model->addFieldFilter('person_firstname', 'person_lastname', '!=')->search()->getResult();
        static::assertCount(1, $res);
        static::assertEqualsCanonicalizing(['Y'], array_column($res, 'person_lastname'));

        // should be one, we only have one parent with same-names (C)
        $model->getNestedJoins('person')[0]->model->addFieldFilter('person_firstname', 'person_lastname');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEqualsCanonicalizing(['A'], array_column($res, 'person_lastname'));

        // see above, non-same-named parents
        $model->getNestedJoins('person')[0]->model->addFieldFilter('person_firstname', 'person_lastname', '!=');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEqualsCanonicalizing(['B'], array_column($res, 'person_lastname'));

        $personModel = $this->getModel('person');
        foreach ($ids as $id) {
            $personModel->delete($id);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAddFieldFilterWithInvalidOperator(): void
    {
        $this->expectExceptionMessage('EXCEPTION_INVALID_OPERATOR');
        $model = $this->getModel('testdata');
        $model->addFieldFilter('testdata_integer', 'testdata_number', 'LIKE'); // like is unsupported
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDefaultfilterSimple(): void
    {
        $model = $this->getModel('testdata');

        // generic default filter
        $model->addDefaultFilter('testdata_number', 3.5, '>');

        $res1 = $model->search()->getResult();
        $res2 = $model->search()->getResult();
        static::assertCount(2, $res1);
        static::assertEquals($res1, $res2);

        // add a filter on the fly - and we expect
        // an empty resultset
        $res = $model
          ->addFilter('testdata_text', 'nonexisting')
          ->search()->getResult();
        static::assertCount(0, $res);

        // try to reduce the resultset to 1
        // in conjunction with the above default filter
        $res = $model
          ->addFilter('testdata_integer', 1, '<=')
          ->search()->getResult();
        static::assertCount(1, $res);
    }

    /**
     * Tests using a discrete model as root
     * and compares equality.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAdhocDiscreteModelAsRoot(): void
    {
        $testdataModel = $this->getModel('testdata');
        $originalRes = $testdataModel->search()->getResult();
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $discreteRes = $discreteModelTest->search()->getResult();
        static::assertEquals($originalRes, $discreteRes);
        // TODO: add some filters and compare again.
    }

    /**
     * Fun with discrete models
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testAdhocDiscreteModelComplex(): void
    {
        $testdataModel = $this->getModel('testdata');
        $testdataModel
          ->hideAllFields()
          ->addField('testdata_id', 'testdataidaliased')
          ->addCalculatedField('calculated', 'testdata_integer * 4')
          ->addGroup('testdata_date')
          ->addModel($this->getModel('details'));
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $res = $discreteModelTest->search()->getResult();

        static::assertCount(3, $res);

        $rootModel = $this->getModel('testdata')->setVirtualFieldResult(true)
          ->addCustomJoin(
              $discreteModelTest,
              join::TYPE_LEFT,
              'testdata_id',
              'testdataidaliased'
          );
        $rootModel->getNestedJoins('sample1')[0]->virtualField = 'virtualSample1';

        $res2 = $rootModel->search()->getResult();

        static::assertCount(4, $res2);
        static::assertEquals([12, null, 4, 168], array_column(array_column($res2, 'virtualSample1'), 'calculated'));

        $secondaryDiscreteModelTest = new discreteDynamic('sample2', $testdataModel);
        $secondaryDiscreteModelTest->addCalculatedField('calcCeption', 'sample2.calculated * sample2.calculated');
        $rootModel->addCustomJoin(
            $secondaryDiscreteModelTest,
            join::TYPE_LEFT,
            'testdata_id',
            'testdataidaliased'
        );
        $rootModel->getNestedJoins('sample2')[0]->virtualField = 'virtualSample2';

        $rootModel->addCalculatedField('calcCeption2', 'sample1.calculated * sample2.calculated');

        $res3 = $rootModel->search()->getResult();

        static::assertEquals([144, null, 16, 28224], array_column(array_column($res3, 'virtualSample2'), 'calcCeption'));
        static::assertEquals([144, null, 16, 28224], array_column($res3, 'calcCeption2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelLimitAndOffset(): void
    {
        $testdataModel = $this->getModel('testdata');
        $testdataModel
          ->hideAllFields()
          ->addField('testdata_id', 'testdataidaliased')
          ->addCalculatedField('calculated', 'testdata_integer * 4')
          // ->addDefaultFilter('testdata_id', 2, '>')
          ->addGroup('testdata_date')
          ->addModel($this->getModel('details'));

        // NOTE limit & offset instances get reset after query
        $testdataModel->setLimit(2)->setOffset(1);

        $originalRes = $testdataModel->search()->getResult();
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);

        // NOTE limit & offset instances get reset after query
        $testdataModel->setLimit(2)->setOffset(1);
        $discreteRes = $discreteModelTest->search()->getResult();

        static::assertCount(2, $discreteRes);
        static::assertEquals($originalRes, $discreteRes);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelAddOrder(): void
    {
        //
        // NOTE ORDER BY in a subquery is ignored in MySQL for final output
        // See https://mariadb.com/kb/en/why-is-order-by-in-a-from-subquery-ignored/
        // But it is essential for LIMIT/OFFSET used in the subquery!
        //
        $testdataModel = $this->getModel('testdata');

        // NOTE order instance gets reset after query
        $testdataModel->addOrder('testdata_id', 'DESC');
        $testdataModel->setOffset(2)->setLimit(2);

        $originalRes = $testdataModel->search()->getResult();
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);

        // NOTE order instance gets reset after query
        $testdataModel->addOrder('testdata_id', 'DESC');
        $testdataModel->setOffset(2)->setLimit(2);
        $discreteRes = $discreteModelTest->search()->getResult();

        static::assertCount(2, $discreteRes);
        static::assertEquals($originalRes, $discreteRes);

        // finally, query the thing with a zero offset
        // to make sure we have ORDER+LIMIT+OFFSET really working
        // inside the subquery
        // though the final order might be different.
        $testdataModel->addOrder('testdata_id', 'DESC');
        $testdataModel->setOffset(0)->setLimit(2);
        $offset0Res = $testdataModel->search()->getResult();

        static::assertNotEquals($offset0Res, $originalRes);

        static::assertLessThan(
            array_sum(array_column($offset0Res, 'testdata_id')), // Offset 0-based results should be topmost => sum of IDs must be greater
            array_sum(array_column($originalRes, 'testdata_id')) // ... and this sum must be LESS THAN the above.
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelSimpleAggregate(): void
    {
        $testdataModel = $this->getModel('testdata')
          ->addAggregateField('id_sum', 'sum', 'testdata_integer')
          ->addGroup('testdata_date')
          ->addDefaultAggregateFilter('id_sum', 10, '<=');

        $originalRes = $testdataModel->search()->getResult();
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);

        $discreteRes = $discreteModelTest->search()->getResult();

        static::assertCount(2, $discreteRes);
        static::assertEquals($originalRes, $discreteRes);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelSaveWillThrow(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $testdataModel = $this->getModel('testdata');
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $discreteModelTest->save(['value' => 'doesnt matter']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelUpdateWillThrow(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $testdataModel = $this->getModel('testdata');
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $discreteModelTest->update(['value' => 'doesnt matter']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelReplaceWillThrow(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $testdataModel = $this->getModel('testdata');
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $discreteModelTest->replace(['value' => 'doesnt matter']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testDiscreteModelDeleteWillThrow(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $testdataModel = $this->getModel('testdata');
        if (!($testdataModel instanceof sql)) {
            static::fail('setup fail');
        }
        $discreteModelTest = new discreteDynamic('sample1', $testdataModel);
        $discreteModelTest->delete(1);
    }

    /**
     * Tests a case where the 'aliased' flag on a group plugin was always active
     * (and ignoring schema/table - on root, there's no currentAlias (null))
     * and causes severe errors when executing a query
     * (ambiguous column)
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testGroupAliasBugFixed(): void
    {
        $model = $this->getModel('person')->setVirtualFieldResult(true)
          ->addModel($this->getModel('person'))
          ->addGroup('person_id');
        $model->search()->getResult();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return void [type] [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testNormalizeData(): void
    {
        $originalDataset = [
          'testdata_datetime' => '2021-04-01 11:22:33',
          'testdata_text' => 'normalizeTest',
          'testdata_date' => '2021-01-01',
        ];

        $normalizeMe = $originalDataset;
        $normalizeMe['crapkey'] = 'crap';

        $model = $this->getModel('testdata');
        $normalized = $model->normalizeData($normalizeMe);
        static::assertEquals($originalDataset, $normalized);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testNormalizeDataComplex(): void
    {
        $fieldComparisons = [
          'testdata_boolean' => [
            'nulled boolean' => [
              'expectedValue' => null,
              'variants' => [
                null,
                '',
              ],
            ],
            'boolean true' => [
              'expectedValue' => true,
              'variants' => [
                true,
                1,
                '1',
                'true',
              ],
            ],
            'boolean false' => [
              'expectedValue' => false,
              'variants' => [
                false,
                0,
                '0',
                'false',
              ],
            ],
          ],
          'testdata_integer' => [
            'nulled integer' => [
              'expectedValue' => null,
              'variants' => [
                null,
                '',
              ],
            ],
          ],
          'testdata_date' => [
            'nulled date' => [
              'expectedValue' => null,
              'variants' => [
                null,
              ],
            ],
              // throws!
              // 'invalid date' => [
              //   'expectedValue' => null,
              //   'variants' => [
              //     '',
              //   ]
              // ],
          ],
        ];

        $model = $this->getModel('testdata');

        foreach ($fieldComparisons as $field => $tests) {
            foreach ($tests as $testName => $test) {
                $expectedValue = $test['expectedValue'];
                foreach ($test['variants'] as $variant) {
                    $normalizeMe = [
                      $field => $variant,
                    ];
                    $expectedDataset = [
                      $field => $expectedValue,
                    ];
                    $normalized = $model->normalizeData($normalizeMe);
                    static::assertEquals($expectedDataset, $normalized, $testName);
                }
            }
        }
    }

    /**
     * @return void [type] [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function testValidateSimple(): void
    {
        $dataset = [
          'testdata_datetime' => '2021-13-01 11:22:33',
          'testdata_text' => ['abc' => true],
          'testdata_date' => '0000-01-01',
        ];

        $model = $this->getModel('testdata');
        static::assertFalse($model->isValid($dataset));

        $validationErrors = $model->validate($dataset)->getErrors();
        static::assertCount(2, $validationErrors); // actually, we should have 3
    }

    /**
     * Tests validation fail with a model-validator
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testModelValidator(): void
    {
        $dataset = [
          'testdata_text' => 'disallowed_value',
        ];
        $model = $this->getModel('testdata');
        static::assertFalse($model->isValid($dataset));
        $validationErrors = $model->validate($dataset)->getErrors();
        static::assertCount(1, $validationErrors);
        static::assertEquals('VALIDATION.FIELD_INVALID', $validationErrors[0]['__CODE']);
        static::assertEquals('testdata_text', $validationErrors[0]['__IDENTIFIER']);
    }

    /**
     * Tests a model-validator that has a non-field-specific validation
     * that affects the whole dataset (e.g. field value combinations that are invalid)
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testModelValidatorSpecial(): void
    {
        $dataset = [
          'testdata_text' => 'disallowed_condition',
          'testdata_date' => '2021-01-01',
        ];
        $model = $this->getModel('testdata');
        static::assertFalse($model->isValid($dataset));
        $validationErrors = $model->validate($dataset)->getErrors();
        static::assertCount(1, $validationErrors);
        static::assertEquals('DATA', $validationErrors[0]['__IDENTIFIER']);
        static::assertEquals('VALIDATION.INVALID', $validationErrors[0]['__CODE']);
        static::assertEquals('VALIDATION.DISALLOWED_CONDITION', $validationErrors[0]['__DETAILS'][0]['__CODE']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testValidateSimpleRequiredField(): void
    {
        $model = $this->getModel('customer');
        //
        // NOTE: the customer model is explicitly loaded
        // w/o the collection model (contactentry)
        // to test for skipping those checks (for coverage)
        //
        static::assertFalse($model->isValid(['customer_notes' => 'missing customer_no']));
        static::assertTrue($model->isValid(['customer_no' => 'ABC', 'customer_notes' => 'set customer_no']));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testValidateCollectionNotUsed(): void
    {
        $model = $this->getModel('customer');
        //
        // NOTE: the customer model is explicitly loaded
        // w/o the collection model (contactentry)
        // to test for skipping those checks (for coverage)
        // but we use the field in the dataset
        //
        static::assertTrue(
            $model->isValid([
              'customer_no' => 'ABC',
              'customer_contactentries' => [
                ['some_value '],
              ],
            ])
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testValidateCollectionData(): void
    {
        $dataset = [
          'customer_no' => 'example',
          'customer_contactentries' => [
            [
              'contactentry_name' => 'test-valid-phone',
              'contactentry_telephone' => '+4929292929292',
            ],
            [
              'contactentry_name' => 'test-invalid-phone',
              'contactentry_telephone' => 'xyz',
            ],
          ],
        ];

        $model = $this->getModel('customer')
          ->addCollectionModel($this->getModel('contactentry'));

        // Just a rough check for invalidity
        static::assertFalse($model->isValid($dataset));
        $validationErrors = $model->validate($dataset)->getErrors();
        static::assertEquals('customer_contactentries', $validationErrors[0]['__IDENTIFIER']);

        $dataset = [
          'customer_no' => 'example2',
          'customer_contactentries' => [
            [
                // no name specified
              'contactentry_telephone' => '+4934343455555',
            ],
          ],
        ];
        // Just a rough check for invalidity
        static::assertFalse($model->isValid($dataset));

        $dataset = [
          'customer_no' => 'example2',
          'customer_contactentries' => [
            [
                // no name specified
              'contactentry_name' => 'some-name', // is required
              'contactentry_telephone' => '+4934343455555',
            ],
          ],
        ];
        // Just a rough check for invalidity
        static::assertTrue($model->isValid($dataset));
    }

    /**
     * Test model::entry* wrapper functions
     * NOTE: they might interfere with regular queries
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryFunctions(): void
    {
        $entryModel = $this->getModel('testdata'); // model used for testing entry* functions
        $model = $this->getModel('testdata'); // model used for querying

        $dataset = [
          'testdata_datetime' => '2021-04-01 11:22:33',
          'testdata_text' => 'entryMakeTest',
          'testdata_date' => '2021-01-01',
          'testdata_number' => 12345.6789,
          'testdata_integer' => 222,
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
          ->addField('testdata_integer');
        $queriedDataset = $model->load($id);
        static::assertEquals($dataset, $queriedDataset);

        $entryModel->entryLoad($id);

        foreach ($dataset as $key => $value) {
            static::assertEquals($value, $entryModel->fieldGet(modelfield::getInstance($key)));
        }

        $entryModel->entryUpdate([
          'testdata_text' => 'updated',
        ]);
        $entryModel->entrySave();

        $modifiedDataset = $model->load($id);
        static::assertEquals('updated', $modifiedDataset['testdata_text']);

        $entryModel->entryLoad($id);
        $entryModel->fieldSet(modelfield::getInstance('testdata_integer'), 333);
        $entryModel->entrySave();

        static::assertEquals(333, $model->load($id)['testdata_integer']);

        $entryModel->entryDelete();
        static::assertEmpty($model->load($id));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryFlags(): void
    {
        $verificationModel = $this->getModel('testdata');
        $model = $this->getModel('testdata');
        $model->entryMake([
          'testdata_text' => 'testEntryFlags',
        ]);
        static::assertEmpty($model->entryValidate());
        $model->entrySave();

        $id = $model->lastInsertId();
        $model->entryLoad($id);

        $model->entrySetFlag($model->getConfig()->get('flag>foo'));
        $model->entrySave();
        static::assertEquals(1, $verificationModel->load($id)['testdata_flag']);

        $model->entrySetFlag($model->getConfig()->get('flag>qux'));
        $model->entrySave();
        static::assertEquals(1 + 8, $verificationModel->load($id)['testdata_flag']);

        $model->entryUnsetFlag($model->getConfig()->get('flag>foo'));
        $model->entryUnsetFlag($model->getConfig()->get('flag>baz')); // unset not-set
        $model->entrySave();
        static::assertEquals(8, $verificationModel->load($id)['testdata_flag']);

        $model->entryDelete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySetFlagNonexisting(): void
    {
        $verificationModel = $this->getModel('testdata');
        $model = $this->getModel('testdata');
        $model->entryMake([
          'testdata_text' => 'testEntrySetFlagNonexisting',
        ]);
        $model->entrySave();

        $id = $model->lastInsertId();
        $model->entryLoad($id);

        // WARNING/NOTE: you can set nonexisting flags
        $model->entrySetFlag(64);
        $model->entrySave();
        static::assertEquals(64, $verificationModel->load($id)['testdata_flag']);

        // WARNING/NOTE: you may set combined flag values
        $model->entrySetFlag(64 + 2 + 8 + 16);
        $model->entryUnsetFlag(8);
        $model->entrySave();
        static::assertEquals(64 + 2 + 16, $verificationModel->load($id)['testdata_flag']);

        $model->entrySave();

        $model->entryDelete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySetFlagInvalidFlagValueThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_INVALID_FLAG_VALUE);
        $model = $this->getModel('testdata');
        $model->entryMake([
          'testdata_text' => 'test',
        ]);
        $model->entrySetFlag(-8);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryUnsetFlagInvalidFlagValueThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_INVALID_FLAG_VALUE);
        $model = $this->getModel('testdata');
        $model->entryMake([
          'testdata_text' => 'test',
        ]);
        $model->entryUnsetFlag(-8);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySetFlagNoDatasetLoadedThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYSETFLAG_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entrySetFlag($model->getConfig()->get('flag>foo'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryUnsetFlagNoDatasetLoadedThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYUNSETFLAG_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entryUnsetFlag($model->getConfig()->get('flag>foo'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySetFlagNoFlagsInModelThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYSETFLAG_NOFLAGSINMODEL);
        $model = $this->getModel('person');
        $model->entryMake(['person_firstname' => 'test']);
        $model->entrySetFlag(1);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryUnsetFlagNoFlagsInModelThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYUNSETFLAG_NOFLAGSINMODEL);
        $model = $this->getModel('person');
        $model->entryMake(['person_firstname' => 'test']);
        $model->entryUnsetFlag(1);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySaveNoDataThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYSAVE_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entrySave(); // we have not defined anything (e.g. internal data store is NULL/does not exist)
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntrySaveEmptyDataThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYSAVE_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entryMake(); // define an empty dataset
        $model->entrySave();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryUpdateEmptyDataThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYUPDATE_UPDATEELEMENTEMPTY);
        $model = $this->getModel('testdata');
        $model->entryUpdate([]); // we've not loaded anything, but this should crash first.
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryUpdateNoDatasetLoaded(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYUPDATE_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entryUpdate(['testdata_integer' => 555]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryLoadNonexistingId(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYLOAD_FAILED);
        $model = $this->getModel('testdata');
        $model->entryLoad(-123);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEntryDeleteNoDatasetLoadedThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_ENTRYDELETE_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->entryDelete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldGetNonexistingThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL);
        $model = $this->getModel('testdata');
        $model->fieldGet(modelfield::getInstance('nonexisting'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldGetNoDatasetLoadedThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_FIELDGET_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->fieldGet(modelfield::getInstance('testdata_integer'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldSetNonexistingThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL);
        $model = $this->getModel('testdata');
        $model->fieldSet(modelfield::getInstance('nonexisting'), 'xyz');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testFieldSetNoDatasetLoadedThrows(): void
    {
        $this->expectExceptionMessage(model::EXCEPTION_FIELDSET_NOOBJECTLOADED);
        $model = $this->getModel('testdata');
        $model->fieldSet(modelfield::getInstance('testdata_integer'), 999);
    }

    /**
     * Basic Timemachine functionality
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testTimemachineDelta(): void
    {
        $testdataTm = $this->getTimemachineEnabledModel('testdata');

        $res = $this->getModel('testdata')
          ->addFilter('testdata_text', 'foo')
          ->addFilter('testdata_date', '2021-03-22')
          ->addFilter('testdata_number', 3.14)
          ->search()->getResult();
        static::assertCount(1, $res);
        $id = $res[0]['testdata_id'];

        $testdataTm->save([
          'testdata_id' => $id,
          'testdata_integer' => 888,
        ]);

        $timemachine = new timemachine($testdataTm);
        $timemachine->getHistory($id);

        $delta = $timemachine->getDeltaData($id, 0);
        static::assertEquals(['testdata_integer' => 3], $delta);

        $bigbangState = $timemachine->getHistoricData($id, 0);
        static::assertEquals(3, $bigbangState['testdata_integer']);

        // restore via delta
        $testdataTm->save(
            array_merge([
              'testdata_id' => $id,
            ], $delta)
        );
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        //
        // make sure data is cleaned in the test...
        //
        $models = ['customer', 'person'];
        foreach ($models as $model) {
            if (!$this->modelDataEmpty($model)) {
                static::fail('Model data not empty: ' . $model);
            }
        }
        // rollback any existing transactions
        // to allow transaction testing
        try {
            $db = app::getDb();
            $db->rollback();
        } catch (\Exception) {
        }

        // perform teardown steps
        parent::tearDown();
    }

    /**
     * @param string $model [description]
     * @return bool          [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function modelDataEmpty(string $model): bool
    {
        return $this->getModel($model)->getCount() === 0;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        $app::__setApp('modeltest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\tests\\model');
        $app::__setHomedir(__DIR__);

        $app::getAppstack();

        // avoid re-init
        if (static::$initialized) {
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
                'driver' => 'memory',
              ],
            ],
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
            'log' => [
              'default' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
            ],
          ],
        ]);

        static::createModel('testschema', 'testdata', [
          'validators' => [
            'model_testdata',
          ],
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
            'testdata_moredetails_id',
            'testdata_flag',
          ],
          'primary' => [
            'testdata_id',
          ],
          'flag' => [
            'foo' => 1,
            'bar' => 2,
            'baz' => 4,
            'qux' => 8,
          ],
          'foreign' => [
            'testdata_details_id' => [
              'schema' => 'testschema',
              'model' => 'details',
              'key' => 'details_id',
            ],
            'testdata_moredetails_id' => [
              'schema' => 'testschema',
              'model' => 'moredetails',
              'key' => 'moredetails_id',
            ],
          ],
          'options' => [
            'testdata_number' => [
              'length' => 16,
              'precision' => 8,
            ],
          ],
          'datatype' => [
            'testdata_id' => 'number_natural',
            'testdata_created' => 'text_timestamp',
            'testdata_modified' => 'text_timestamp',
            'testdata_datetime' => 'text_timestamp',
            'testdata_details_id' => 'number_natural',
            'testdata_moredetails_id' => 'number_natural',
            'testdata_text' => 'text',
            'testdata_date' => 'text_date',
            'testdata_number' => 'number',
            'testdata_integer' => 'number_natural',
            'testdata_boolean' => 'boolean',
            'testdata_structure' => 'structure',
            'testdata_flag' => 'number_natural',
          ],
          'connection' => 'default',
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
            'details_id',
          ],
          'datatype' => [
            'details_id' => 'number_natural',
            'details_created' => 'text_timestamp',
            'details_modified' => 'text_timestamp',
            'details_data' => 'structure',
            'details_virtual' => 'virtual',
          ],
          'connection' => 'default',
        ]);

        static::createModel('testschema', 'moredetails', [
          'field' => [
            'moredetails_id',
            'moredetails_created',
            'moredetails_modified',
            'moredetails_data',
          ],
          'primary' => [
            'moredetails_id',
          ],
          'datatype' => [
            'moredetails_id' => 'number_natural',
            'moredetails_created' => 'text_timestamp',
            'moredetails_modified' => 'text_timestamp',
            'moredetails_data' => 'structure',
          ],
          'connection' => 'default',
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
            'table1_id',
          ],
          'foreign' => [
            'multi_component_fkey' => [
              'schema' => 'multi_fkey',
              'model' => 'table2',
              'key' => [
                'table1_key1' => 'table2_key1',
                'table1_key2' => 'table2_key2',
              ],
              'optional' => true,
            ],
          ],
          'options' => [
            'table1_key1' => [
              'length' => 16,
            ],
          ],
          'datatype' => [
            'table1_id' => 'number_natural',
            'table1_created' => 'text_timestamp',
            'table1_modified' => 'text_timestamp',
            'table1_key1' => 'text',
            'table1_key2' => 'number_natural',
            'table1_value' => 'text',
          ],
          'connection' => 'default',
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
            'table2_id',
          ],
          'options' => [
            'table2_key1' => [
              'length' => 16,
            ],
          ],
          'datatype' => [
            'table2_id' => 'number_natural',
            'table2_created' => 'text_timestamp',
            'table2_modified' => 'text_timestamp',
            'table2_key1' => 'text',
            'table2_key2' => 'number_natural',
            'table2_value' => 'text',
          ],
          'connection' => 'default',
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
            'customer_id',
          ],
          'unique' => [
            'customer_no',
          ],
          'required' => [
            'customer_no',
          ],
          'children' => [
            'customer_person' => [
              'type' => 'foreign',
              'field' => 'customer_person_id',
            ],
            'customer_contactentries' => [
              'type' => 'collection',
            ],
          ],
          'collection' => [
            'customer_contactentries' => [
              'schema' => 'vfields',
              'model' => 'contactentry',
              'key' => 'contactentry_customer_id',
            ],
          ],
          'foreign' => [
            'customer_person_id' => [
              'schema' => 'vfields',
              'model' => 'person',
              'key' => 'person_id',
            ],
          ],
          'options' => [
            'customer_no' => [
              'length' => 16,
            ],
          ],
          'datatype' => [
            'customer_id' => 'number_natural',
            'customer_created' => 'text_timestamp',
            'customer_modified' => 'text_timestamp',
            'customer_no' => 'text',
            'customer_person_id' => 'number_natural',
            'customer_person' => 'virtual',
            'customer_contactentries' => 'virtual',
            'customer_notes' => 'text',
          ],
          'connection' => 'default',
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
            'contactentry_id',
          ],
          'required' => [
            'contactentry_name',
          ],
          'foreign' => [
            'contactentry_customer_id' => [
              'schema' => 'vfields',
              'model' => 'customer',
              'key' => 'customer_id',
            ],
          ],
          'datatype' => [
            'contactentry_id' => 'number_natural',
            'contactentry_created' => 'text_timestamp',
            'contactentry_modified' => 'text_timestamp',
            'contactentry_name' => 'text',
            'contactentry_telephone' => 'text_telephone',
            'contactentry_customer_id' => 'number_natural',
          ],
          'connection' => 'default',
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
            'person_id',
          ],
          'children' => [
            'person_parent' => [
              'type' => 'foreign',
              'field' => 'person_parent_id',
            ],
          ],
          'foreign' => [
            'person_parent_id' => [
              'schema' => 'vfields',
              'model' => 'person',
              'key' => 'person_id',
            ],
            'person_country' => [
              'schema' => 'json',
              'model' => 'country',
              'key' => 'country_code',
            ],
          ],
          'options' => [
            'person_country' => [
              'length' => 2,
            ],
          ],
          'datatype' => [
            'person_id' => 'number_natural',
            'person_created' => 'text_timestamp',
            'person_modified' => 'text_timestamp',
            'person_firstname' => 'text',
            'person_lastname' => 'text',
            'person_birthdate' => 'text_date',
            'person_country' => 'text',
            'person_parent_id' => 'number_natural',
            'person_parent' => 'virtual',
          ],
          'connection' => 'default',
        ]);

        static::createModel('json', 'country', [
          'field' => [
            'country_code',
            'country_name',
          ],
          'primary' => [
            'country_code',
          ],
          'datatype' => [
            'country_code' => 'text',
            'country_name' => 'text',
          ],
            // No connection, JSON datamodel
        ], function ($schema, $model, $config) {
            return new jsonModel(
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
            'timemachine_id',
          ],
          'required' => [
            'timemachine_model',
            'timemachine_ref',
            'timemachine_data',
          ],
          'index' => [
            ['timemachine_model', 'timemachine_ref'],
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
            'timemachine_id' => 'number_natural',
            'timemachine_created' => 'text_timestamp',
            'timemachine_modified' => 'text_timestamp',
            'timemachine_model' => 'text',
            'timemachine_ref' => 'number_natural',
            'timemachine_data' => 'structure',
            'timemachine_source' => 'text',
            'timemachine_user_id' => 'number_natural',
          ],
          'connection' => 'default',
        ]);


        static::architect('modeltest', 'codename', 'test');

        static::createTestData();
    }

    /**
     * should return a database config for 'default' connection
     * @return array
     */
    abstract protected function getDefaultDatabaseConfig(): array;

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected static function createTestData(): void
    {
        // Just to make sure... initial cleanup
        // If there has been a shutdown failure after the last test
        // if this executed using a still running DB.
        static::deleteTestData();

        $testdataModel = static::getModelStatic('testdata');

        $entries = [
          [
            'testdata_text' => 'foo',
            'testdata_datetime' => '2021-03-22 12:34:56',
            'testdata_date' => '2021-03-22',
            'testdata_number' => 3.14,
            'testdata_integer' => 3,
            'testdata_structure' => ['foo' => 'bar'],
            'testdata_boolean' => true,
          ],
          [
            'testdata_text' => 'bar',
            'testdata_datetime' => '2021-03-22 12:34:56',
            'testdata_date' => '2021-03-22',
            'testdata_number' => 4.25,
            'testdata_integer' => 2,
            'testdata_structure' => ['foo' => 'baz'],
            'testdata_boolean' => true,
          ],
          [
            'testdata_text' => 'foo',
            'testdata_datetime' => '2021-03-23 23:34:56',
            'testdata_date' => '2021-03-23',
            'testdata_number' => 5.36,
            'testdata_integer' => 1,
            'testdata_structure' => ['boo' => 'far'],
            'testdata_boolean' => false,
          ],
          [
            'testdata_text' => 'bar',
            'testdata_datetime' => '2019-01-01 00:00:01',
            'testdata_date' => '2019-01-01',
            'testdata_number' => 0.99,
            'testdata_integer' => 42,
            'testdata_structure' => ['bar' => 'foo'],
            'testdata_boolean' => false,
          ],
        ];

        foreach ($entries as $dataset) {
            $testdataModel->save($dataset);
        }
    }

    /**
     * @param array $config [description]
     * @return database         [description]
     */
    abstract protected function getDatabaseInstance(array $config): database;
}

/**
 * Overridden timemachine class
 * that allows setting an instance directly (and skip app::getModel internally)
 * - needed for these 'staged' unit tests
 */
class overrideableTimemachine extends timemachine
{
    /**
     * @param model $modelInstance [description]
     * @param string $app [description]
     * @param string $vendor [description]
     * @return void [type]                [description]
     * @throws exception
     */
    public static function storeInstance(model $modelInstance, string $app = '', string $vendor = ''): void
    {
        $capableModelName = $modelInstance->getIdentifier();
        $identifier = $capableModelName . '-' . $vendor . '-' . $app;
        self::$instances[$identifier] = new self($modelInstance);
    }
}

class timemachineEnabledSqlModel extends sqlModel implements timemachineInterface
{
    /**
     * @var model|null
     */
    protected ?model $timemachineModelInstance = null;

    /**
     * {@inheritDoc}
     */
    public function isTimemachineEnabled(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimemachineModel(): model
    {
        return $this->timemachineModelInstance;
    }

    /**
     * @param model $instance
     * @return void
     */
    public function setTimemachineModelInstance(model $instance): void
    {
        $this->timemachineModelInstance = $instance;
    }
}

class timemachineModel extends sqlModel implements timemachineModelInterface
{
    /**
     * current identity, null if not retrieved yet
     * @var array|null
     */
    protected ?array $identity = null;

    /**
     * {@inheritDoc}
     */
    public function save(array $data): model
    {
        if ($data[$this->getPrimaryKey()] ?? null) {
            throw new exception('TIMEMACHINE_UPDATE_DENIED', exception::$ERRORLEVEL_FATAL);
        } else {
            $data = array_replace($data, $this->getIdentity());
            return parent::save($data);
        }
    }

    /**
     * Get identity parameters for injecting
     * into the timemachine dataset
     * @return array
     */
    protected function getIdentity(): array
    {
        if (!$this->identity) {
            $this->identity = [
              'timemachine_source' => 'unittest',
              'timemachine_user_id' => 123,
            ];
        }
        return $this->identity;
    }

    /**
     * {@inheritDoc}
     */
    public function getModelField(): string
    {
        return 'timemachine_model';
    }

    /**
     * {@inheritDoc}
     */
    public function getRefField(): string
    {
        return 'timemachine_ref';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataField(): string
    {
        return 'timemachine_data';
    }
}
