<?php

namespace codename\core\tests\model;

use codename\core\config;
use codename\core\exception;
use codename\core\model;
use codename\core\model\abstractDynamicValueModel;
use codename\core\tests\base;
use ReflectionException;

/**
 * Tests a specialized model class
 */
class abstractDynamicValueModelTest extends base
{
    /**
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$initialized = false;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testBasicADVMEmpty(): void
    {
        $model = new advmTestModel($this->getModel('advm_data'));
        static::assertEquals([
          [
            'advm_test' => null,
            'some_integer' => null,
            'some_text' => null,
            'some_boolean' => null,
            'some_structure' => null,
            'some_timestamp' => null,
          ],
        ], $model->search()->getResult());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testBasicADVMSetValueTwice(): void
    {
        $model = new advmTestModel($this->getModel('advm_data'));
        $model->save([
          'some_integer' => 1,
        ]);
        $model->save([
          'some_integer' => 2,
        ]);
        static::assertEquals(2, $model->load('advm_test')['some_integer']);
    }

    /**
     * @testWith [ "some_integer", 3 ]
     *           [ "some_integer", null ]
     *           [ "some_text", "test1" ]
     *           [ "some_boolean", true ]
     *           [ "some_boolean", false ]
     *           [ "some_structure", { "a": "b" } ]
     *           [ "some_structure", null ]
     *           [ "some_timestamp", "2021-11-11" ]
     *           [ "some_timestamp", "2021-11-11 12:34:56" ]
     * @param string $field [description]
     * @param mixed $value [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testBasicADVMSetValue(string $field, mixed $value): void
    {
        $model = new advmTestModel($this->getModel('advm_data'));
        $dataset = [
          $field => $value,
        ];
        static::assertTrue($model->isValid($dataset));
        $model->save($dataset);
        static::assertEquals($value, $model->load('advm_test')[$field]);
    }

    /**
     * @testWith [ "some_integer", "abc" ]
     *           [ "some_integer", false ]
     *           [ "some_text", false ]
     *           [ "some_boolean", "abc" ]
     *           [ "some_boolean", 123 ]
     *           [ "some_timestamp", "abc" ]
     *           [ "some_timestamp", 123 ]
     *           [ "some_timestamp", true ]
     * @param string $field [description]
     * @param mixed $value [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testBasicADVMInvalidValue(string $field, mixed $value): void
    {
        $model = new advmTestModel($this->getModel('advm_data'));
        $dataset = [
          $field => $value,
        ];
        static::assertFalse($model->isValid($dataset));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testBasicADVMFieldlist(): void
    {
        $model = new advmTestModel($this->getModel('advm_data'));
        $model->hideAllFields();
        $model->addField('some_integer');
        static::assertEquals([
          [
            'some_integer' => null,
          ],
        ], $model->search()->getResult());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testAdvancedADVMEmpty(): void
    {
        $model = new advmDomainTestModel($this->getModel('advm_domain_data'));
        static::assertEquals([
          [
            'advm_domain_test' => null,
            'some_integer' => null,
            'some_text' => null,
            'some_boolean' => null,
            'some_structure' => null,
            'some_timestamp' => null,
          ],
        ], $model->search()->getResult());
        //
        $model->addFilter('advm_domain_test', 1);

        static::assertEquals([
          [
            'advm_domain_test' => 1,
            'some_integer' => null,
            'some_text' => null,
            'some_boolean' => null,
            'some_structure' => null,
            'some_timestamp' => null,
          ],
        ], $model->search()->getResult());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testAdvancedADVMSave(): void
    {
        $model = new advmDomainTestModel($this->getModel('advm_domain_data'));
        $model->save([
          'advm_domain_test' => 1,
          'some_integer' => 123,
        ]);
        $model->addFilter('advm_domain_test', 1);
        static::assertEquals([
          [
            'advm_domain_test' => 1,
            'some_integer' => 123,
            'some_text' => null,
            'some_boolean' => null,
            'some_structure' => null,
            'some_timestamp' => null,
          ],
        ], $model->search()->getResult());

        $model->addFilter('advm_domain_test', 2);
        static::assertEquals([
          [
            'advm_domain_test' => 2,
            'some_integer' => null,
            'some_text' => null,
            'some_boolean' => null,
            'some_structure' => null,
            'some_timestamp' => null,
          ],
        ], $model->search()->getResult());
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        // clean up the data model
        $this->getModel('advm_data')
          ->addFilter('advm_data_id', 0, '>')
          ->delete();

        parent::tearDown();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        // Additional overrides to get a more complete app lifecycle
        // and allow static global app::getModel() to work correctly
        $app::__setApp('advmtest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\tests\\model');

        $app::getAppstack();

        // avoid re-init
        if (static::$initialized) {
            return;
        }

        static::$initialized = true;

        static::setEnvironmentConfig([
          'test' => [
            'database' => [
                // NOTE: by default, we do these tests using
                // pure in-memory sqlite.
              'default' => [
                'driver' => 'sqlite',
                'database_file' => ':memory:',
              ],
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

        //
        // Base/data model for an abstractDynamicValueModel
        //
        static::createModel('testschema', 'advm_data', [
          'field' => [
            'advm_data_id',
            'advm_data_created',
            'advm_data_modified',
            'advm_data_name',
            'advm_data_datatype',
            'advm_data_value',
          ],
          'primary' => [
            'advm_data_id',
          ],
          'unique' => [
            'advm_data_name',
          ],
          'required' => [
            'advm_data_name',
            'advm_data_datatype',
          ],
          'options' => [
            'advm_data_name' => [
              'length' => 128,
            ],
          ],
          'datatype' => [
            'advm_data_id' => 'number_natural',
            'advm_data_created' => 'text_timestamp',
            'advm_data_modified' => 'text_timestamp',
            'advm_data_name' => 'text',
            'advm_data_datatype' => 'text',
            'advm_data_value' => 'text',
          ],
          'connection' => 'default',
        ]);

        //
        // Base/data model for an abstractDynamicValueModel
        //
        static::createModel('testschema', 'advm_domain_data', [
          'field' => [
            'advm_domain_data_id',
            'advm_domain_data_created',
            'advm_domain_data_modified',
            'advm_domain_data_domain_id',
            'advm_domain_data_name',
            'advm_domain_data_datatype',
            'advm_domain_data_value',
          ],
          'primary' => [
            'advm_domain_data_id',
          ],
          'unique' => [
            ['advm_domain_data_domain_id', 'advm_domain_data_name'],
          ],
          'required' => [
            'advm_domain_data_name',
            'advm_domain_data_datatype',
          ],
          'options' => [
            'advm_domain_data_name' => [
              'length' => 128,
            ],
          ],
          'datatype' => [
            'advm_domain_data_id' => 'number_natural',
            'advm_domain_data_created' => 'text_timestamp',
            'advm_domain_data_modified' => 'text_timestamp',
            'advm_domain_data_domain_id' => 'number_natural',
            'advm_domain_data_name' => 'text',
            'advm_domain_data_datatype' => 'text',
            'advm_domain_data_value' => 'text',
          ],
          'connection' => 'default',
        ]);

        static::architect('advmtest', 'codename', 'test');
    }
}

/**
 * Basic implementation of an ADVM
 */
class advmTestModel extends abstractDynamicValueModel
{
    /**
     * [__construct description]
     * @param model $dataModel [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(model $dataModel)
    {
        parent::__construct([]);

        $this->dataModel = $dataModel;

        //
        // only provide an unspecific pkey (no association with data model)
        //
        $this->setPrimaryKey('advm_test');

        $this->setDataModelConfig(
            'advm_data_name',      // some unique key (or unique component) for identifying the variable
            'advm_data_datatype',  // the datatype field
            'advm_data_value'      // the value field
        );
        $this->setDynamicConfig(
            null,
            new config([
              'some_integer' => [
                "datatype" => "number_natural",
              ],
              'some_text' => [
                "datatype" => "text",
              ],
              'some_boolean' => [
                "datatype" => "boolean",
              ],
              'some_structure' => [
                "datatype" => "structure",
              ],
              'some_timestamp' => [
                "datatype" => "text_timestamp",
              ],
            ])
        );
        $this->loadConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return 'advm_test';
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeDataModel(): void
    {
    }
}


/**
 * More advanced implementation of an ADVM
 */
class advmDomainTestModel extends abstractDynamicValueModel
{
    /**
     * @param model $dataModel [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(model $dataModel)
    {
        parent::__construct([]);

        $this->dataModel = $dataModel;

        //
        // only provide an unspecific pkey (no association with data model)
        //
        $this->setPrimaryKey('advm_domain_test', 'advm_domain_data_domain_id');

        $this->setDataModelConfig(
            'advm_domain_data_name',      // some unique key (or unique component) for identifying the variable
            'advm_domain_data_datatype',  // the datatype field
            'advm_domain_data_value'      // the value field
        );
        $this->setDynamicConfig(
            null,
            new config([
              'some_integer' => [
                "datatype" => "number_natural",
              ],
              'some_text' => [
                "datatype" => "text",
              ],
              'some_boolean' => [
                "datatype" => "boolean",
              ],
              'some_structure' => [
                "datatype" => "structure",
              ],
              'some_timestamp' => [
                "datatype" => "text_timestamp",
              ],
            ])
        );
        $this->loadConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return 'advm_domain_test';
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeDataModel(): void
    {
    }
}
