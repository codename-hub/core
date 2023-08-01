<?php

namespace codename\core\tests;

use codename\core\exception;
use codename\core\model\abstractDynamicValueModel;
use codename\core\model\schematic\sql;
use ReflectionException;

/**
 * Test Virtual field functionality
 */
class virtualFieldsTest extends base
{
    /**
     * Tests saving virtual field data with enabled models
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualFieldSaving(): void
    {
        $model2 = $this->getModel('model2')->setVirtualFieldResult(true)
          ->addModel($model1 = $this->getModel('model1')->setVirtualFieldResult(true));

        if (!($model2 instanceof sql) && !($model2 instanceof abstractDynamicValueModel)) {
            static::fail('model2 fail');
        }

        $model2->saveWithChildren([
          'model2_value' => 'm2',
          'model2_model1' => [
            'model1_value' => 'm1',
          ],
        ]);

        // Assert we have a lastInsertId generated in *both* models
        static::assertGreaterThan(0, $model1->lastInsertId());
        static::assertGreaterThan(0, $model2->lastInsertId());
    }

    /**
     * Tests saving virtual field data with enabled models
     * and checks result output, including hidden fields
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualFieldWithRedactedFields(): void
    {
        $model2 = $this->getModel('model2')->setVirtualFieldResult(true)
          ->addModel($model1 = $this->getModel('model1')->setVirtualFieldResult(true));

        if (!($model2 instanceof sql) && !($model2 instanceof abstractDynamicValueModel)) {
            static::fail('model2 fail');
        }

        $model2->saveWithChildren([
          'model2_value' => 'abc',
          'model2_model1' => [
            'model1_value' => 'def',
          ],
        ]);
        $id = $model2->lastInsertId();

        // just allow one field for the child model
        $model1->hideAllFields()->addField('model1_value');

        $res = $model2
          ->addFilter($model2->getPrimaryKey(), $id)
          ->search()->getResult();

        // Make sure we have the right count being returned
        // and the data is the same as in the beginning
        // NOTE: we have reduced field output here!
        static::assertCount(1, $res);
        static::assertEquals(['model1_value' => 'def'], $res[0]['model2_model1']);
    }

    /**
     * Tests saving virtual field data with enabled models
     * and checks result output, including hidden fields
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualFieldWithCollections(): void
    {
        $model2 = $this->getModel('model2')->setVirtualFieldResult(true)
          ->addCollectionModel($this->getModel('model3'), 'model2_model3_items')
          ->addModel($this->getModel('model1')->setVirtualFieldResult(true));

        if (!($model2 instanceof sql) && !($model2 instanceof abstractDynamicValueModel)) {
            static::fail('model2 fail');
        }

        $model2->saveWithChildren([
          'model2_value' => 'xyz',
          'model2_model3_items' => [
            ['model3_value' => 'first'],
            ['model3_value' => 'second'],
            ['model3_value' => 'third'],
          ],
          'model2_model1' => [
            'model1_value' => 'vwu',
          ],
        ]);
        $id = $model2->lastInsertId();

        $res = $model2
          ->addFilter($model2->getPrimaryKey(), $id)
          ->search()->getResult();

        // Make sure we have the right count being returned
        // and the data is the same as in the beginning
        // NOTE: we have reduced field output here!
        static::assertCount(1, $res);

        static::assertEquals(['first', 'second', 'third'], array_column($res[0]['model2_model3_items'], 'model3_value'));
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
            'database' => [
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

        static::createModel('schema1', 'model1', [
          'field' => [
            'model1_id',
            'model1_created',
            'model1_modified',
            'model1_value',
          ],
          'primary' => [
            'model1_id',
          ],
          'datatype' => [
            'model1_id' => 'number_natural',
            'model1_created' => 'text_timestamp',
            'model1_modified' => 'text_timestamp',
            'model1_value' => 'text',
          ],
          'connection' => 'default',
        ]);

        //
        // A secondary model with a reference to model1
        // including a virtual field that may display/represent a model1-dataset
        //
        static::createModel('schema2', 'model2', [
          'field' => [
            'model2_id',
            'model2_created',
            'model2_modified',
            'model2_value',
            'model2_model1_id',
            'model2_model1',
            'model2_model3_items',
          ],
          'primary' => [
            'model2_id',
          ],
          'children' => [
            'model2_model1' => [
              'type' => 'foreign',
              'field' => 'model2_model1_id',
            ],
            'model2_model3_items' => [
              'type' => 'collection',
            ],
          ],
          'collection' => [
            'model2_model3_items' => [
              'schema' => 'schema3',
              'model' => 'model3',
              'key' => 'model3_model2_id',
            ],
          ],
          'foreign' => [
            'model2_model1_id' => [
              'schema' => 'schema1',
              'model' => 'model1',
              'key' => 'model1_id',
            ],
          ],
          'datatype' => [
            'model2_id' => 'number_natural',
            'model2_created' => 'text_timestamp',
            'model2_modified' => 'text_timestamp',
            'model2_value' => 'text',
            'model2_model1_id' => 'number_natural',
            'model2_model1' => 'virtual',
            'model2_model3_items' => 'virtual',
          ],
          'connection' => 'default',
        ]);
        //
        // A secondary model with a reference to model1
        // including a virtual field that may display/represent a model1-dataset
        //
        static::createModel('schema3', 'model3', [
          'field' => [
            'model3_id',
            'model3_created',
            'model3_modified',
            'model3_value',
            'model3_model2_id',
          ],
          'primary' => [
            'model3_id',
          ],
          'foreign' => [
            'model3_model2_id' => [
              'schema' => 'schema2',
              'model' => 'model2',
              'key' => 'model2_id',
            ],
          ],
          'datatype' => [
            'model3_id' => 'number_natural',
            'model3_created' => 'text_timestamp',
            'model3_modified' => 'text_timestamp',
            'model3_value' => 'text',
            'model3_model2_id' => 'number_natural',
          ],
          'connection' => 'default',
        ]);

        static::architect('vfieldstest', 'codename', 'test');
    }
}
