<?php
namespace codename\core\tests\extension;

use codename\core\app;

// use codename\core\test\dummyTemplateengine;
// use codename\core\test\cliExitPreventResponse;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

/**
 * Test some generic extension-related routines
 */
class extensionTest extends base {

  /**
   * [protected description]
   * @var overrideableApp
   */
  protected $appInstance = null;

  // /**
  //  * @inheritDoc
  //  */
  // protected function tearDown(): void
  // {
  //   $this->appInstance->__setInstance('request', null);
  //   $this->appInstance->__setInstance('response', null);
  //   parent::tearDown();
  //   $this->appInstance->reset();
  // }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    // overrideableApp::__overrideJsonConfigPath('tests/lifecycle/contextTest.app.json');

    $this->appInstance = $this->createApp();
    $this->appInstance->__setApp('exampleapp');
    $this->appInstance->__setVendor('codename');
    $this->appInstance->__setNamespace('\\codename\\core\\tests\\extension\\exampleapp');
    $this->appInstance->__setHomedir(__DIR__.'/exampleapp');

    $this->appInstance->getAppstack();

    static::setEnvironmentConfig([
      'test' => [
        // 'database' => [
        //   'default' => [
        //     'driver' => 'sqlite',
        //     'database_file' => ':memory:',
        //   ]
        // ],
        'templateengine' => [
          'default' => [
            "driver" => "dummy"
          ]
        ],
        'session' => [
          'default' => [
            'driver' => 'dummy'
          ]
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
          ],
          'debug' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ],
          'access' => [
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
   * [testExtensionLoaded description]
   */
  public function testExtensionLoaded(): void {
    $appstack = app::getAppstack();
    $this->assertEquals('exampleextension', $appstack[1]['app']);
  }

  /**
   * [testExtensionClientAvailable description]
   */
  public function testExtensionClientAvailable(): void {
    $class = app::getInheritedClass('database_exttest');
    $instance = new $class([]);
    $this->assertInstanceOf(\codename\core\tests\extension\exampleextension\database\exttest::class, $instance);
  }

  /**
   * [testExtensionNotLoaded description]
   */
  public function testExtensionNotLoaded(): void {
    // Reset app to make sure extension is not injected
    $this->appInstance->reset();
    $class = app::getInheritedClass('database_exttest');
    $this->assertFalse(class_exists($class));
  }

  /**
   * [testExtensionCouldNotBeLoaded description]
   */
  public function testExtensionCouldNotBeLoaded(): void {
    // Reset app to make sure we have a clean starting point
    $this->appInstance->reset();

    $this->appInstance = $this->createApp();
    $this->appInstance->__setApp('nonexistingext');
    $this->appInstance->__setVendor('codename');
    $this->appInstance->__setNamespace('\\codename\\core\\tests\\extension\\nonexistingext');
    $this->appInstance->__setHomedir(__DIR__.'/nonexistingext');

    $this->expectExceptionMessage('CORE_APP_EXTENSION_COULD_NOT_BE_LOADED');
    $this->appInstance->getAppstack();
  }
}
