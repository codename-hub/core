<?php
namespace codename\core\tests\lifecycle;

use codename\core\tests\base;

class appGetModelTest extends base {

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

    // Additional overrides to get a more complete app lifecycle
    // and allow static global app::getModel() to work correctly
    $app->__setApp('lifecycletest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\tests\\lifecycle');

    $app->getAppstack();

    // avoid re-init
    if(static::$initialized) {
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

    static::createModel('lifecycle', 'sample', [
      'field' => [
        'sample_id',
        'sample_created',
        'sample_modified',
        'sample_text',
      ],
      'primary' => [
        'sample_id'
      ],
      'datatype' => [
        'sample_id'       => 'number_natural',
        'sample_created'  => 'text_timestamp',
        'sample_modified' => 'text_timestamp',
        'sample_text'     => 'text',
      ],
      'connection' => 'default'
    ]);

    static::architect('lifecycletest', 'codename', 'test');

  }

  /**
   * [testAppGetModel description]
   */
  public function testAppGetModel(): void {
    $sampleModel = \codename\core\app::getModel('sample');
    $this->assertEquals([
      'sample_id',
      'sample_created',
      'sample_modified',
      'sample_text',
    ], $sampleModel->getFields());
  }

  /**
   * [testAppGetModelAgain description]
   */
  public function testAppGetModelAgain(): void {
    $sampleModel = \codename\core\app::getModel('sample');
    $this->assertEquals([
      'sample_id',
      'sample_created',
      'sample_modified',
      'sample_text',
    ], $sampleModel->getFields());
  }

}
