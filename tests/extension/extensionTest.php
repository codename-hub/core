<?php

namespace codename\core\tests\extension;

use codename\core\app;
use codename\core\exception;
use codename\core\tests\base;
use codename\core\tests\extension\exampleextension\database\exttest;
use codename\core\tests\overrideableApp;
use ReflectionException;

// use codename\core\test\dummyTemplateengine;
// use codename\core\test\cliExitPreventResponse;

/**
 * Test some generic extension-related routines
 */
class extensionTest extends base
{
    /**
     * [protected description]
     * @var \codename\core\test\overrideableApp|overrideableApp|null
     */
    protected \codename\core\test\overrideableApp|null|overrideableApp $appInstance = null;

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtensionLoaded(): void
    {
        $appstack = app::getAppstack();
        static::assertEquals('exampleextension', $appstack[1]['app']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtensionClientAvailable(): void
    {
        $class = app::getInheritedClass('database_exttest');
        $instance = new $class([]);
        static::assertInstanceOf(exttest::class, $instance);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtensionNotLoaded(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        // Reset app to make sure extension is not injected
        $this->appInstance::reset();
        $class = app::getInheritedClass('database_exttest');
        static::assertFalse(class_exists($class));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtensionCouldNotBeLoaded(): void
    {
        if (!($this->appInstance instanceof \codename\core\test\overrideableApp)) {
            static::fail('setup fail');
        }
        // Reset app to make sure we have a clean starting point
        $this->appInstance::reset();

        $this->appInstance = static::createApp();
        $this->appInstance::__setApp('nonexistingext');
        $this->appInstance::__setVendor('codename');
        $this->appInstance::__setNamespace('\\codename\\core\\tests\\extension\\nonexistingext');
        $this->appInstance::__setHomedir(__DIR__ . '/nonexistingext');

        $this->expectExceptionMessage('CORE_APP_EXTENSION_COULD_NOT_BE_LOADED');
        $this->appInstance::getAppstack();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $this->appInstance = static::createApp();
        $this->appInstance::__setApp('exampleapp');
        $this->appInstance::__setVendor('codename');
        $this->appInstance::__setNamespace('\\codename\\core\\tests\\extension\\exampleapp');
        $this->appInstance::__setHomedir(__DIR__ . '/exampleapp');

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
