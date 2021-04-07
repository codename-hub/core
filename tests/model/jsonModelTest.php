<?php
namespace codename\core\tests\model;

use codename\core\exception;

use codename\core\model\timemachineModelInterface;

use codename\core\tests\base;

/**
 * Base model class performing cross-platform/technology tests with model classes
 */
class jsonModelTest extends base {

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
        // 'database' => [
        //   'default' => $this->getDefaultDatabaseConfig(),
        // ],
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

    static::createModel('json', 'example', [
      'field' => [
        'example_id',
        'example_text',
        'example_integer',
        'example_number',
      ],
      'primary' => [
        'example_id'
      ],
      'datatype' => [
        'example_id'      => 'text',
        'example_text'    => 'text',
        'example_integer' => 'number_natural',
        'example_number'  => 'number',
      ],
      // No connection, JSON datamodel
    ], function($schema, $model, $config) {
      return new \codename\core\tests\jsonModel(
        'tests/model/data/json_example.json',
        $schema,
        $model,
        $config
      );
    });
  }

  /**
   * [testFilters description]
   */
  public function testFilters(): void {
    $model = $this->getModel('example');

    // no filters
    $res = $model->search()->getResult();
    $this->assertCount(3, $res);

    // filters for text value
    $model->addFilter('example_text', 'bar');
    $res = $model->search()->getResult();
    $this->assertCount(1, $res);
    $this->assertEquals($res[0][$model->getPrimarykey()], 'SECOND');

    // filters for text value LIKE
    $model->addFilter('example_text', 'ba%', 'LIKE');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);

    // filters for text value NOT EQUAL
    $model->addFilter('example_text', 'bar', '!=');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);

    // filters for GT
    $model->addFilter('example_integer', 234, '>');
    $res = $model->search()->getResult();
    $this->assertCount(1, $res);

    // filters for GTE
    $model->addFilter('example_integer', 234, '>=');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);

    // filters for LT
    $model->addFilter('example_integer', 234, '<');
    $res = $model->search()->getResult();
    $this->assertCount(1, $res);

    // filters for LTE
    $model->addFilter('example_integer', 234, '<=');
    $res = $model->search()->getResult();
    $this->assertCount(2, $res);

    // multiple filters
    $model
      ->addFilter('example_integer', 300, '<=')
      ->addFilter('example_number', 20.1, '>')
      ->addFilter('example_text', 'baz', '!=')
      ;
    $res = $model->search()->getResult();
    $this->assertCount(1, $res);
    $this->assertEquals($res[0][$model->getPrimarykey()], 'SECOND');
  }

  // /**
  //  * [testFiltercollections description]
  //  */
  // public function testFiltercollections(): void {
  //   $model = $this->getModel('example');
  //   $model->addFilterCollection([
  //     [ 'field' => 'example_text',    'operator' => '=', 'value' => 'foo' ],
  //     [ 'field' => 'example_integer', 'operator' => '=', 'value' => 234 ],
  //   ], 'OR');
  //   $res = $model->search()->getResult();
  //   $this->assertCount(2, $res);
  // }
}
