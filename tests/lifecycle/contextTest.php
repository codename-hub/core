<?php
namespace codename\core\tests\lifecycle;

use codename\core\app;

use codename\core\test\dummyTemplateengine;
use codename\core\test\cliExitPreventResponse;

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
    $this->appInstance->__setInstance('request', null);
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

  /**
   * [testContextIsAllowedReturnsFalseAndAppRunForbidden description]
   */
  public function testContextIsAllowedReturnsFalseAndAppRunForbidden(): void {
    $this->appInstance->getRequest()->setData('template', '');
    $this->appInstance->getRequest()->setData('context', 'disallowedcontext');
    $data = $this->appInstance->getResponse()->getData();

    // Response instance is not stored in $_REQUEST, but instead bootstrap::$instances
    // $_REQUEST['response'] = new cliExitPreventResponse($data);
    $this->appInstance->__setInstance('response', new cliExitPreventResponse($data));

    $contextInstance = new disallowedcontext();
    $this->appInstance->__injectContextInstance('disallowedcontext', $contextInstance);
    $this->appInstance->__injectClientInstance('templateengine', 'default', new dummyTemplateengine);

    // Add a callback into app run end hook/event
    $appRunForbidden = null;
    $this->appInstance->getHook()->add(\codename\core\hook::EVENT_APP_RUN_FORBIDDEN, function() use (&$appRunForbidden) {
      $appRunForbidden = true;
    });

    $this->appInstance->run();

    // Check if callback (see above) had been called successfully
    $this->assertTrue($appRunForbidden);
  }

  /**
   * Tests accessing an undefined context
   */
  public function testAppNonexistingContext(): void {
    $this->expectExceptionMessage(\codename\core\app::EXCEPTION_MAKEREQUEST_CONTEXT_CONFIGURATION_MISSING);

    $this->appInstance->getRequest()->setData('template', '');
    $this->appInstance->getRequest()->setData('context', 'nonexisting');

    $this->appInstance->__injectClientInstance('templateengine', 'default', new dummyTemplateengine);
    $this->appInstance->__setInstance('response', new cliThrowExceptionResponse);
    $this->appInstance->run();
  }

  /**
   * Tests accessing an undefined view
   */
  public function testAppNonexistingView(): void {
    $this->expectExceptionMessage(\codename\core\app::EXCEPTION_MAKEREQUEST_REQUESTEDVIEWNOTINCONTEXT);

    $this->appInstance->getRequest()->setData('template', '');
    $this->appInstance->getRequest()->setData('context', 'testcontext');
    $this->appInstance->getRequest()->setData('view', 'nonexisting'); // Nonexisting view

    $this->appInstance->__injectClientInstance('templateengine', 'default', new dummyTemplateengine);
    $this->appInstance->__injectContextInstance('testcontext', new testcontext);
    $this->appInstance->__setInstance('response', new cliThrowExceptionResponse);
    $this->appInstance->run();
  }

  /**
   * Tests case when the view function is defined in app.json
   * but unavailable in class
   */
  public function testAppNonexistingViewFunction(): void {
    $this->expectExceptionMessage(\codename\core\app::EXCEPTION_DOVIEW_VIEWFUNCTIONNOTFOUNDINCONTEXT);

    $this->appInstance->getRequest()->setData('template', '');
    $this->appInstance->getRequest()->setData('context', 'testcontext');
    $this->appInstance->getRequest()->setData('view', 'nonexisting_function'); // Nonexisting view function

    $this->appInstance->__injectClientInstance('templateengine', 'default', new dummyTemplateengine);
    $this->appInstance->__injectContextInstance('testcontext', new testcontext);
    $this->appInstance->__setInstance('response', new cliThrowExceptionResponse);

    $this->appInstance->run();
  }

  /**
   * [testViewLevelTemplate description]
   */
  public function testViewLevelTemplate(): void {
    $this->appInstance->getRequest()->setData('context', 'templatelevel');
    $this->appInstance->getRequest()->setData('view', 'viewlevel_template');
    $this->appInstance->__injectContextInstance('templatelevel', new testcontext);
    $this->appInstance->run();
    $this->assertEquals('viewlevel', $this->appInstance->getRequest()->getData('template'));
  }

  /**
   * [testContextLevelTemplate description]
   */
  public function testContextLevelTemplate(): void {
    $this->appInstance->getRequest()->setData('context', 'templatelevel');
    $this->appInstance->getRequest()->setData('view', 'contextlevel_template');
    $this->appInstance->__injectContextInstance('templatelevel', new testcontext);
    $this->appInstance->run();
    $this->assertEquals('contextlevel', $this->appInstance->getRequest()->getData('template'));
  }

  /**
   * [testAppLevelTemplate description]
   */
  public function testAppLevelTemplate(): void {
    $this->appInstance->getRequest()->setData('context', 'templatefallback');
    $this->appInstance->getRequest()->setData('view', 'default');
    $this->appInstance->__injectContextInstance('templatefallback', new testcontext);
    $this->appInstance->run();
    $this->assertEquals('blank', $this->appInstance->getRequest()->getData('template'));
  }
}

/**
 * helper class for really throw the exception
 * that is usually displayed
 */
class cliThrowExceptionResponse extends cliExitPreventResponse {
  /**
   * @inheritDoc
   */
  public function displayException(\Exception $e)
  {
    throw $e;
  }
}

/**
 * dummy context, bare minimum
 */
class testcontext extends \codename\core\context {
  public function view_default() {}
}

/**
 * a context class that simply should not be accessible
 */
class disallowedcontext extends \codename\core\context {
  /**
   * @inheritDoc
   */
  public function isAllowed(): bool
  {
    return false;
  }

  public function view_default() {}
}
