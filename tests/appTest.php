<?php
namespace codename\core\tests;

use codename\core\app;

/**
 * Test some generic app-class functions
 */
class appTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {

    $app = $this->createApp();
    $app->getAppstack();

    static::setEnvironmentConfig([
      'test' => [
        // 'database' => [
        //   'default' => [
        //     'driver' => 'sqlite',
        //     'database_file' => ':memory:',
        //   ]
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
  }

  /**
   * [testGetInheritedClassNonexisting description]
   */
  public function testGetInheritedClassNonexisting(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(app::EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND);
    $class = app::getInheritedClass('nonexisting');
  }

  /**
   * [testGetInheritedClassExisting description]
   */
  public function testGetInheritedClassExisting(): void {
    $class = app::getInheritedClass('database');
    $this->assertEquals('\\codename\\core\\database', $class);
  }
}
