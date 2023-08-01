<?php

namespace codename\core\tests\model;

use codename\core\model\plugin\join;
use codename\core\tests\base;
use codename\core\tests\jsonModel;
use Exception;
use ReflectionException;

/**
 * Base model class performing cross-platform/technology tests with model classes
 */
class jsonModelTest extends base
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
     * @throws \codename\core\exception
     */
    public function testSaveThrowsException(): void
    {
        $this->expectException(Exception::class);
        $model = $this->getModel('example');
        $model->save([
          'example_text' => 'new_must_not_save',
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDeleteThrowsException(): void
    {
        $this->expectException(Exception::class);
        $model = $this->getModel('example');
        $model->delete('FIRST');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testVirtualFields(): void
    {
        $model = $this->getModel('example');
        $model->addVirtualField('example_virtual', function ($dataset) {
            return $dataset['example_text'] . $dataset['example_integer'];
        });
        $dataset = $model->load('SECOND');
        static::assertEquals('bar234', $dataset['example_virtual']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFilters(): void
    {
        $model = $this->getModel('example');

        // no filters
        $res = $model->search()->getResult();
        static::assertCount(3, $res);

        // load
        $dataset = $model->load('FIRST');
        static::assertEquals('foo', $dataset['example_text']);

        // filter for PKEY
        $model->addFilter('example_id', 'THIRD');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // filters for text value
        $model->addFilter('example_text', 'bar');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals('SECOND', $res[0][$model->getPrimaryKey()]);

        // filters for text value LIKE
        $model->addFilter('example_text', 'ba%', 'LIKE');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        // filters for text value NOT EQUAL
        $model->addFilter('example_text', 'bar', '!=');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        // filters for GT
        $model->addFilter('example_integer', 234, '>');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // filters for GTE
        $model->addFilter('example_integer', 234, '>=');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        // filters for LT
        $model->addFilter('example_integer', 234, '<');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // filters for LTE
        $model->addFilter('example_integer', 234, '<=');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        // special PKEY filter for IN()
        $model->addFilter('example_id', ['SECOND', 'invalid']);
        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // filters for IN()
        $model->addFilter('example_text', ['foo', 'baz']);
        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        // filters for NOT IN()
        $model->addFilter('example_text', ['foo', 'baz'], '!=');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);

        // multiple filters
        $model
          ->addFilter('example_integer', 300, '<=')
          ->addFilter('example_number', 20.1, '>')
          ->addFilter('example_text', 'baz', '!=');
        $res = $model->search()->getResult();
        static::assertCount(1, $res);
        static::assertEquals('SECOND', $res[0][$model->getPrimaryKey()]);

        // basic OR filtering
        $model
          ->addFilter('example_integer', 200, '<=')
          ->addFilter('example_number', 32.1, '>', 'OR');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);
        static::assertContainsEquals('FIRST', array_column($res, $model->getPrimaryKey()));
        static::assertContainsEquals('THIRD', array_column($res, $model->getPrimaryKey()));

        // multiple contrary filters
        $model
          ->addFilter('example_integer', 500, '>')
          ->addFilter('example_integer', 500, '<');
        $res = $model->search()->getResult();
        static::assertCount(0, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFiltercollections(): void
    {
        $model = $this->getModel('example');
        $model->addFilterCollection([
          ['field' => 'example_text', 'operator' => '=', 'value' => 'foo'],
          ['field' => 'example_integer', 'operator' => '=', 'value' => 234],
        ], 'OR');
        $res = $model->search()->getResult();
        static::assertCount(2, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testNamedFiltercollections(): void
    {
        $model = $this->getModel('example');

        // will match all
        $model->addDefaultFilterCollection([
            // will match FIRST, SECOND
          ['field' => 'example_text', 'operator' => '=', 'value' => 'foo'],
          ['field' => 'example_integer', 'operator' => '=', 'value' => 234],
        ], 'OR', 'g1');
        $model->addDefaultFilterCollection([
            // will match SECOND, THIRD
          ['field' => 'example_text', 'operator' => '!=', 'value' => 'foo'],
          ['field' => 'example_integer', 'operator' => '=', 'value' => 345],
        ], 'OR', 'g1', 'OR');

        $res = $model->search()->getResult();
        static::assertCount(3, $res);

        $model->addFilterCollection([
            // will match SECOND
          ['field' => 'example_text', 'operator' => '=', 'value' => 'bar'],
          ['field' => 'example_integer', 'operator' => '=', 'value' => 999],
        ], 'OR', 'g2');
        $model->addFilterCollection([
            // will match THIRD
          ['field' => 'example_text', 'operator' => '=', 'value' => 'baz'],
          ['field' => 'example_number', 'operator' => '>=', 'value' => 30],
        ], 'AND', 'g2', 'OR');

        $res = $model->search()->getResult();

        static::assertCount(2, $res);
        static::assertEqualsCanonicalizing(['SECOND', 'THIRD'], array_column($res, $model->getPrimaryKey()));


        $model->addFilterCollection([
            // will FIRST, THIRD
          ['field' => 'example_text', 'operator' => '=', 'value' => ['foo', 'baz']],
        ], 'AND', 'g3', 'OR');

        $res = $model->search()->getResult();

        static::assertCount(2, $res);
        static::assertEqualsCanonicalizing(['FIRST', 'THIRD'], array_column($res, $model->getPrimaryKey()));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSimpleJoin(): void
    {
        $model = $this->getModel('example')
          ->addModel($this->getModel('country'));
        $res = $model->search()->getResult();
        static::assertEquals(['Germany', 'Austria', null], array_column($res, 'country_name'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSimpleInnerJoin(): void
    {
        $model = $this->getModel('example')
          ->addModel(
              $this->getModel('country'),
              join::TYPE_INNER
          );
        $res = $model->search()->getResult();
        static::assertEquals(['Germany', 'Austria'], array_column($res, 'country_name'));
    }

    /**
     * Right join with json/bare datamodels is explicitly unsupported
     * Make sure the respective exception is thrown.
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testRightJoinWillFail(): void
    {
        $this->expectExceptionMessage('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE');
        $model = $this->getModel('example')
          ->addModel(
              $this->getModel('country'),
              join::TYPE_RIGHT
          );
        $model->search()->getResult();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        // avoid re-init
        if (static::$initialized) {
            return;
        }

        static::$initialized = true;

        static::setEnvironmentConfig([
          'test' => [
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

        static::createModel('json', 'example', [
          'field' => [
            'example_id',
            'example_text',
            'example_integer',
            'example_number',
            'example_country',
          ],
          'primary' => [
            'example_id',
          ],
          'foreign' => [
            'example_country' => [
              'model' => 'country',
              'key' => 'country_code',
            ],
          ],
          'datatype' => [
            'example_id' => 'text',
            'example_text' => 'text',
            'example_integer' => 'number_natural',
            'example_number' => 'number',
            'example_country' => 'text',
          ],
            // No connection, JSON datamodel
        ], function ($schema, $model, $config) {
            return new jsonModel(
                'tests/model/data/json_example.json',
                $schema,
                $model,
                $config
            );
        });
    }
}
