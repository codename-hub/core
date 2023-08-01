<?php

namespace codename\core\tests\config;

use codename\core\config\json;
use codename\core\config\json\extendable;
use codename\core\exception;
use codename\core\test\overrideableApp;
use codename\core\tests\base;
use ReflectionException;

class jsonTest extends base
{
    /**
     * @return void
     */
    public function testSimpleJsonConfig(): void
    {
        $config = new json('tests/config/example.json');
        $original = json_decode(file_get_contents(__DIR__ . '/example.json'), true);
        static::assertEquals($original, $config->get());
    }

    /**
     * @return void
     */
    public function testEmptyJsonFileWillThrow(): void
    {
        $this->expectExceptionMessage(json::EXCEPTION_DECODEFILE_FILEISEMPTY);
        new json('tests/config/empty.json');
    }

    /**
     * @return void
     */
    public function testInvalidJsonFileWillThrow(): void
    {
        $this->expectExceptionMessage(json::EXCEPTION_DECODEFILE_FILEISINVALID);
        new json('tests/config/invalid.json');
    }

    /**
     * @return void
     */
    public function testExtendedJsonConfig(): void
    {
        $config = new extendable('tests/config/example.extends.json');
        static::assertEquals('some-overridden-value', $config->get('some-key'));
        static::assertEquals('value1', $config->get('some-object>key1'));
        static::assertEquals('value-changed', $config->get('some-object>key2'));
        static::assertEquals('value3', $config->get('some-object>key3'));
        static::assertEquals('value-added', $config->get('some-object>key4'));

        // Mixin, root key added
        static::assertEquals('mixed-in-value', $config->get('mixed-in-key'));

        // Mixin, root key merged
        static::assertEquals(['some-value2', 'other-value'], $config->get('some-key2'));

        // Mixin adds a value to this key, making it an array
        static::assertEquals(['value5', 'added-value'], $config->get('some-object>key5'));

        // Root is being overridden by extends, mixin is merged
        static::assertEquals(['some-item-2?', 'new-item'], $config->get('some-array'));
    }

    /**
     * Tests whether loading a config with appstack && !inherit crashes
     * @return void
     */
    public function testExtendedJsonConfigInvalidInitParams(): void
    {
        $this->expectExceptionMessage(json::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR);

        //
        // We can't use inheritance without the appstack
        //
        new extendable('sample.json', false, true);
    }

    /**
     * Tests whether loading a config with appstack && !inherit crashes
     * @return void
     */
    public function testJsonConfigInvalidInitParams(): void
    {
        $this->expectExceptionMessage(json::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR);

        //
        // We can't use inheritance without the appstack
        //
        new json('sample.json', false, true);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtendedJsonAbsolutePathWithAppstack(): void
    {
        $config = new extendable(__DIR__ . '/app1/sample.json', true, true);
        static::assertEquals('value2', $config->get('key2'));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testJsonAbsolutePathWithAppstack(): void
    {
        // NOTE: due to internal usage of realpath
        // we simply work around platform test differences
        // by inserting the platform-dependent directory separators right here
        $config = new json(__DIR__ . DIRECTORY_SEPARATOR . 'app1' . DIRECTORY_SEPARATOR . 'sample.json', true, true);
        static::assertEquals('value2', $config->get('key2'));
    }

    /**
     * config\json: First-match config loading
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testJsonAppstackNoInheritance(): void
    {
        overrideableApp::reset();
        overrideableApp::__setApp('app1');
        overrideableApp::__setVendor('irrelevant');
        overrideableApp::__setHomedir(__DIR__ . '/app1');

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected',
          'homedir' => __DIR__ . '/app_injected',
          'namespace' => '--irrelevant--',
        ]);

        //
        // We traverse the appstack,
        // but we load the first matching config only.
        //
        $config = new json('otherSample.json', true, false);
        static::assertTrue($config->get('otherSample'));
    }

    /**
     * config\json\extendable: First-match config loading
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtendedJsonAppstackNoInheritance(): void
    {
        overrideableApp::reset();
        overrideableApp::__setApp('app1');
        overrideableApp::__setVendor('irrelevant');
        overrideableApp::__setHomedir(__DIR__ . '/app1');

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected',
          'homedir' => __DIR__ . '/app_injected',
          'namespace' => '--irrelevant--',
        ]);

        //
        // We traverse the appstack,
        // but we load the first matching config only.
        //
        $config = new extendable('otherSample.json', true, false);
        static::assertTrue($config->get('otherSample'));
    }

    /**
     * config\json\extendable: Traverse appstack, but no file exists anywhere.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtendedJsonAppstackInheritanceNonexistingFile(): void
    {
        overrideableApp::reset();
        overrideableApp::__setApp('app1');
        overrideableApp::__setVendor('irrelevant');
        overrideableApp::__setHomedir(__DIR__ . '/app1');

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected',
          'homedir' => __DIR__ . '/app_injected',
          'namespace' => '--irrelevant--',
        ]);

        $this->expectExceptionMessage(json::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND);

        //
        // We traverse the appstack,
        // but we load the first matching config only.
        //
        new extendable('nonexisting.json', true, true);
    }

    /**
     * config\json: Traverse appstack, but no file exists anywhere.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testJsonAppstackInheritanceNonexistingFile(): void
    {
        overrideableApp::reset();
        overrideableApp::__setApp('app1');
        overrideableApp::__setVendor('irrelevant');
        overrideableApp::__setHomedir(__DIR__ . '/app1');

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected',
          'homedir' => __DIR__ . '/app_injected',
          'namespace' => '--irrelevant--',
        ]);

        $this->expectExceptionMessage(json::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND);

        //
        // We traverse the appstack,
        // but we load the first matching config only.
        //
        new json('nonexisting.json', true, true);
    }

    /**
     * Tests a more complex case with inheritance, appstack, mixins and extends
     * and keys that override each other
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExtendedJsonConfigAppstackInheritance(): void
    {
        overrideableApp::reset();
        overrideableApp::__setApp('app1');
        overrideableApp::__setVendor('irrelevant');
        overrideableApp::__setHomedir(__DIR__ . '/app1');

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected',
          'homedir' => __DIR__ . '/app_injected',
          'namespace' => '--irrelevant--',
        ]);

        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected_extends',
          'homedir' => __DIR__ . '/app_injected_extends',
          'namespace' => '--irrelevant--',
        ]);
        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected_mixin',
          'homedir' => __DIR__ . '/app_injected_mixin',
          'namespace' => '--irrelevant--',
        ]);
        overrideableApp::__injectApp([
          'vendor' => 'irrelevant',
          'app' => 'app_injected_overrides',
          'homedir' => __DIR__ . '/app_injected_overrides',
          'namespace' => '--irrelevant--',
        ]);

        $config = new extendable('sample.json', true, true);

        static::assertEquals('value1', $config->get('key1'));
        static::assertEquals('value2', $config->get('key2'));
        static::assertEquals('overridden', $config->get('overrideMe'));
        static::assertTrue($config->get('extend1'));
        static::assertTrue($config->get('mixin1'));
        static::assertTrue($config->get('extend1-extends'));
        static::assertTrue($config->get('mixin1-mixin'));

        $expectedInheritanceRegexes = [
          '/app_injected_mixin\/sample.json/',
          '/app_injected_extends\/sample.json/',
          '/app_injected\/sample.json/',
          '/app1\/sample.json/',
        ];

        $inheritance = $config->getInheritance();

        // make sure we have no unexpected inherited elements
        static::assertCount(count($expectedInheritanceRegexes), $inheritance);

        foreach ($expectedInheritanceRegexes as $index => $regex) {
            static::assertMatchesRegularExpression($regex, $inheritance[$index]);
        }
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
          ],
        ]);
    }
}
