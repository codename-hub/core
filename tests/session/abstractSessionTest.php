<?php
namespace codename\core\tests\bucket;

use codename\core\app;

use codename\core\tests\base;

abstract class abstractSessionTest extends base {

  /**
   * should return a database config for 'default' connection
   * @return array
   */
  protected abstract function getDefaultSessionConfig(): array;

  /**
   * [getAdditionalEnvironmentConfig description]
   * @return array [description]
   */
  protected function getAdditionalEnvironmentConfig(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();
    $app->getAppstack();
    
    static::setEnvironmentConfig([
      'test' => array_merge([
        'session' => [
          'default' => $this->getDefaultSessionConfig()
        ],
        // 'filesystem' =>[
        //   'local' => [
        //     'driver' => 'local',
        //   ]
        // ],
        // 'log' => [
        //   'debug' => [
        //     'driver' => 'system',
        //     'data' => [
        //       'name' => 'dummy'
        //     ]
        //   ]
        // ]
      ],
      $this->getAdditionalEnvironmentConfig()
    )]);
  }

  /**
   * [testUnidentified description]
   */
  public function testUnidentified(): void {
    $this->assertFalse(app::getSession()->identify());
  }

  /**
   * [testStart description]
   */
  public function testBasicIo(): void {
    $this->assertFalse(app::getSession()->identify());
    app::getSession()->start([
      'dummy' => true,
    ]);
    $this->assertTrue(app::getSession()->identify());
    $this->assertTrue(app::getSession()->isDefined('dummy'));
    $this->assertFalse(app::getSession()->isDefined('nonexisting'));

    $this->assertEquals(true, app::getSession()->getData('dummy'));

    app::getSession()->setData('dummy', 'some-value');
    $this->assertEquals('some-value', app::getSession()->getData('dummy'));

    // TODO: Not supported for every driver right now:
    // app::getSession()->unsetData('dummy');
    // $this->assertFalse(app::getSession()->isDefined('dummy'));

    app::getSession()->destroy();

    $this->assertFalse(app::getSession()->identify());
  }

}
