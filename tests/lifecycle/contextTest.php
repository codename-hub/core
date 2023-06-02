<?php

namespace codename\core\tests\lifecycle;

use codename\core\app;
use codename\core\context;
use codename\core\hook;
use codename\core\test\cliExitPreventResponse;
use codename\core\test\dummyTemplateengine;
use codename\core\tests\base;
use codename\core\tests\overrideableApp;
use Exception;
use ReflectionException;

/**
 * Test some generic context-related routines
 */
class contextTest extends base
{
    /**
     * [protected description]
     * @var \codename\core\test\overrideableApp|overrideableApp|null
     */
    protected \codename\core\test\overrideableApp|null|overrideableApp $appInstance = null;

    /**
     * Tests a simple runtime cycle - from init/start to end.
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testRuntimeCycle(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }

        $this->appInstance::getRequest()->setData('template', '');
        $this->appInstance::getRequest();
        $this->appInstance::getResponse()->getData();

        // Response instance is not stored in $_REQUEST, but instead bootstrap::$instances
        // $_REQUEST['response'] = new cliExitPreventResponse();
        $this->appInstance::__setInstance('response', new cliExitPreventResponse());

        $contextInstance = new testcontext();
        $this->appInstance::__injectContextInstance('testcontext', $contextInstance);
        $this->appInstance::__injectClientInstance('templateengine', 'default', new dummyTemplateengine());
        $this->appInstance::getRequest()->setData('context', 'testcontext');

        // Make sure we have our custom response instance
        // to prevent some side effects (e.g. exiting prematurely)
        static::assertInstanceOf(cliExitPreventResponse::class, $this->appInstance::getResponse());

        // Add a callback into app run end hook/event
        $appRunEnd = false;
        $this->appInstance::getHook()->add(hook::EVENT_APP_RUN_END, function () use (&$appRunEnd) {
            $appRunEnd = true;
        });

        $this->appInstance->run();

        // Check if callback (see above) had been called successfully
        static::assertTrue($appRunEnd);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testContextIsAllowedReturnsFalseAndAppRunForbidden(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }

        $this->appInstance::getRequest()->setData('template', '');
        $this->appInstance::getRequest()->setData('context', 'disallowedcontext');
        $this->appInstance::getResponse()->getData();

        // Response instance is not stored in $_REQUEST, but instead bootstrap::$instances
        // $_REQUEST['response'] = new cliExitPreventResponse($data);
        $this->appInstance::__setInstance('response', new cliExitPreventResponse());

        $contextInstance = new disallowedcontext();
        $this->appInstance::__injectContextInstance('disallowedcontext', $contextInstance);
        $this->appInstance::__injectClientInstance('templateengine', 'default', new dummyTemplateengine());

        // Add a callback into app run end hook/event
        $appRunForbidden = null;
        $this->appInstance::getHook()->add(hook::EVENT_APP_RUN_FORBIDDEN, function () use (&$appRunForbidden) {
            $appRunForbidden = true;
        });

        $this->appInstance->run();

        // Check if callback (see above) had been called successfully
        static::assertTrue($appRunForbidden);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAppNonexistingContext(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->expectExceptionMessage(app::EXCEPTION_MAKEREQUEST_CONTEXT_CONFIGURATION_MISSING);

        $this->appInstance::getRequest()->setData('template', '');
        $this->appInstance::getRequest()->setData('context', 'nonexisting');

        $this->appInstance::__injectClientInstance('templateengine', 'default', new dummyTemplateengine());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());
        $this->appInstance->run();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAppNonexistingView(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->expectExceptionMessage(app::EXCEPTION_MAKEREQUEST_REQUESTEDVIEWNOTINCONTEXT);

        $this->appInstance::getRequest()->setData('template', '');
        $this->appInstance::getRequest()->setData('context', 'testcontext');
        $this->appInstance::getRequest()->setData('view', 'nonexisting'); // Nonexisting view

        $this->appInstance::__injectClientInstance('templateengine', 'default', new dummyTemplateengine());
        $this->appInstance::__injectContextInstance('testcontext', new testcontext());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());
        $this->appInstance->run();
    }

    /**
     * Tests case when the view function is defined in app.json
     * but unavailable in class
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAppNonexistingViewFunction(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->expectExceptionMessage(app::EXCEPTION_DOVIEW_VIEWFUNCTIONNOTFOUNDINCONTEXT);

        $this->appInstance::getRequest()->setData('template', '');
        $this->appInstance::getRequest()->setData('context', 'testcontext');
        $this->appInstance::getRequest()->setData('view', 'nonexisting_function'); // Nonexisting view function

        $this->appInstance::__injectClientInstance('templateengine', 'default', new dummyTemplateengine());
        $this->appInstance::__injectContextInstance('testcontext', new testcontext());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());

        $this->appInstance->run();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testViewLevelTemplate(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->appInstance::getRequest()->setData('context', 'templatelevel');
        $this->appInstance::getRequest()->setData('view', 'viewlevel_template');
        $this->appInstance::__injectContextInstance('templatelevel', new testcontext());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());

        $this->appInstance->run();
        static::assertEquals('viewlevel', $this->appInstance::getRequest()->getData('template'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testContextLevelTemplate(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->appInstance::getRequest()->setData('context', 'templatelevel');
        $this->appInstance::getRequest()->setData('view', 'contextlevel_template');
        $this->appInstance::__injectContextInstance('templatelevel', new testcontext());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());

        $this->appInstance->run();
        static::assertEquals('contextlevel', $this->appInstance::getRequest()->getData('template'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAppLevelTemplate(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->appInstance::getRequest()->setData('context', 'templatefallback');
        $this->appInstance::getRequest()->setData('view', 'default');
        $this->appInstance::__injectContextInstance('templatefallback', new testcontext());
        $this->appInstance::__setInstance('response', new cliThrowExceptionResponse());

        $this->appInstance->run();
        static::assertEquals('blank', $this->appInstance::getRequest()->getData('template'));
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        $this->appInstance::__setInstance('request', null);
        $this->appInstance::__setInstance('response', null);
        parent::tearDown();
        $this->appInstance::reset();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    protected function setUp(): void
    {
        overrideableApp::__overrideJsonConfigPath('tests/lifecycle/contextTest.app.json');

        $this->appInstance = static::createApp();

        // $this->appInstance->__setApp('');
        $this->appInstance::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
            'templateengine' => [
              'default' => [
                "driver" => "dummy",
              ],
            ],
            'session' => [
              'default' => [
                'driver' => 'dummy',
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
              'debug' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
              'access' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
            ],
          ],
        ]);
    }
}

/**
 * helper class for really throw the exception
 * that is usually displayed
 */
class cliThrowExceptionResponse extends cliExitPreventResponse
{
    /**
     * {@inheritDoc}
     * @param Exception $e
     * @throws Exception
     */
    public function displayException(Exception $e): void
    {
        throw $e;
    }
}

/**
 * dummy context, bare minimum
 */
class testcontext extends context
{
    /**
     * @return void
     */
    public function view_default(): void
    {
    }
}

/**
 * a context class that simply should not be accessible
 */
class disallowedcontext extends context
{
    /**
     * {@inheritDoc}
     */
    public function isAllowed(): bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function view_default(): void
    {
    }
}
