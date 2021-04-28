<?php
namespace codename\core\tests\lifecycle;

use codename\core\app;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

/**
 * Test some generic context-related routines
 */
class contextTest extends base {

  /**
   * [protected description]
   * @var overrideableApp
   */
  protected $appInstance = null;

  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    $this->appInstance->__setInstance('response', null);
    parent::tearDown();
    $this->appInstance->reset();
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    overrideableApp::__overrideJsonConfigPath('tests/lifecycle/contextTest.app.json');

    $this->appInstance = $this->createApp();

    // $this->appInstance->__setApp('');
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
   * Tests a simple runtime cycle - from init/start to end.
   * @return void
   */
  public function testRuntimeCycle(): void {

    echo("testRuntimeCycle".chr(10));
    $this->appInstance->getRequest()->setData('template', '');
    $this->appInstance->getRequest();
    $data = $this->appInstance->getResponse()->getData();

    // Response instance is not stored in $_REQUEST, but instead bootstrap::$instances
    // $_REQUEST['response'] = new cliExitPreventResponse($data);
    $this->appInstance->__setInstance('response', new cliExitPreventResponse($data));

    $contextInstance = new testcontext();
    $this->appInstance->__injectContextInstance('testcontext', $contextInstance);
    $this->appInstance->__injectClientInstance('templateengine', 'default', new dummyTemplateengine);
    $this->appInstance->getRequest()->setData('context', 'testcontext');

    // Make sure we have our custom response instance
    // to prevent some side-effects (e.g. exiting prematurely)
    $this->assertInstanceOf(cliExitPreventResponse::class, $this->appInstance->getResponse());

    // Add a callback into app run end hook/event
    $appRunEnd = false;
    $this->appInstance->getHook()->add(\codename\core\hook::EVENT_APP_RUN_END, function() use (&$appRunEnd) {
      $appRunEnd = true;
    });

    $this->appInstance->run();

    // Check if callback (see above) had been called successfully
    $this->assertTrue($appRunEnd);
  }

}

class cliExitPreventResponse extends \codename\core\response\cli {
  /**
   * @inheritDoc
   */
  public function pushOutput()
  {
    return;
  }
}

class testcontext extends \codename\core\context {
  public function view_default() {
  }
}

class dummyTemplateengine extends \codename\core\templateengine {
  /**
   * @inheritDoc
   */
  public function render(string $referencePath, $data = null): string
  {
    return '';
  }

  /**
   * @inheritDoc
   */
  public function renderView(string $viewPath, $data = null): string
  {
    return '';
  }

  /**
   * @inheritDoc
   */
  public function renderTemplate(string $templatePath, $data = null): string
  {
    return '';
  }
}
