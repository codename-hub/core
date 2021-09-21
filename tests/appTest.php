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

    try {
      $app->getAppstack();
    } catch (\codename\core\exception $e) {
      if($e->getMessage() === app::EXCEPTION_GETAPP_APPFOLDERNOTFOUND) {
        // DEBUG
        print_r([
          'message' => $e->getMessage(),
          'file'    => $e->getLine(),
          'code'    => $e->getCode(),
          'info'    => $e->info,
          'additionals' => [
            '__FILE__' => __FILE__,
            '__DIR__' => __DIR__,

            'apploader' => debugApp::getApploaderPublic(),
            'apploader_explode' => explode('\\', debugApp::getApploaderPublic()->get()),
            // 'appInstance' => debugApp::getInstancePublic(),

            // emulate internal apploader

            // 'apploader' => explode('\\', app::getApploader()->get()),
          ]
        ]);
      }
      throw $e; // re-throw
    }


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

class debugApp extends \codename\core\test\overrideableApp {
  public static function getInstancePublic() {
    return static::$instance;
  }
  public static function getApploaderPublic(): \codename\core\value\text\apploader {
    return static::getApploader();
  }
}
