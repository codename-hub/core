<?php
namespace codename\core\tests\config;

use codename\core\test\overrideableApp;

use codename\core\tests\base;

class jsonTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();
    $app->getAppstack();

    static::setEnvironmentConfig([
      'test' => [
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        // 'log' => [
        //   'errormessage' => [
        //     'driver' => 'system',
        //     'data' => [
        //       'name' => 'dummy'
        //     ]
        //   ],
        //   'debug' => [
        //     'driver' => 'system',
        //     'data' => [
        //       'name' => 'dummy'
        //     ]
        //   ]
        // ],
      ]
    ]);
  }

  /**
   * [testSimpleJsonConfig description]
   */
  public function testSimpleJsonConfig(): void {
    $config = new \codename\core\config\json('tests/config/example.json');
    $original = json_decode(file_get_contents(__DIR__.'/example.json'),true);
    $this->assertEquals($original, $config->get());
  }

  /**
   * [testEmptyJsonFileWillThrow description]
   */
  public function testEmptyJsonFileWillThrow(): void {
    $this->expectExceptionMessage(\codename\core\config\json::EXCEPTION_DECODEFILE_FILEISEMPTY);
    $config = new \codename\core\config\json('tests/config/empty.json');
  }

  /**
   * [testInvalidJsonFileWillThrow description]
   */
  public function testInvalidJsonFileWillThrow(): void {
    $this->expectExceptionMessage(\codename\core\config\json::EXCEPTION_DECODEFILE_FILEISINVALID);
    $config = new \codename\core\config\json('tests/config/invalid.json');
  }

  /**
   * [testExtendedJsonConfig description]
   */
  public function testExtendedJsonConfig(): void {
    $config = new \codename\core\config\json\extendable('tests/config/example.extends.json');
    $this->assertEquals('some-overridden-value', $config->get('some-key'));
    $this->assertEquals('value1',         $config->get('some-object>key1'));
    $this->assertEquals('value-changed',  $config->get('some-object>key2'));
    $this->assertEquals('value3',         $config->get('some-object>key3'));
    $this->assertEquals('value-added',    $config->get('some-object>key4'));

    // Mixin, root key added
    $this->assertEquals('mixed-in-value',    $config->get('mixed-in-key'));

    // Mixin, root key merged
    $this->assertEquals(['some-value2', 'other-value'],    $config->get('some-key2'));

    // Mixin adds a value to this key, making it an array
    $this->assertEquals(['value5', 'added-value'],    $config->get('some-object>key5'));

    // Root is being overridden by extends, mixin is merged
    $this->assertEquals(['some-item-2?', 'new-item'],    $config->get('some-array'));

    // TODO: arrays?

    // print_r($config->get());
  }

  /**
   * Tests whether loading a config with appstack && !inherit crashes
   */
  public function testExtendedJsonConfigInvalidInitParams(): void {
    $this->expectErrorMessage(\codename\core\config\json::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR);

    //
    // We can't use inheritance without the appstack
    //
    $config = new \codename\core\config\json\extendable('sample.json', false, true);
  }

  /**
   * Tests whether loading a config with appstack && !inherit crashes
   */
  public function testJsonConfigInvalidInitParams(): void {
    $this->expectErrorMessage(\codename\core\config\json::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR);

    //
    // We can't use inheritance without the appstack
    //
    $config = new \codename\core\config\json('sample.json', false, true);
  }

  /**
   * [testAbsolutePathWithAppstack description]
   */
  public function testExtendedJsonAbsolutePathWithAppstack(): void {
    $config = new \codename\core\config\json\extendable(__DIR__.'/app1/sample.json', true, true);
    $this->assertEquals('value2', $config->get('key2'));
  }

  /**
   * [testAbsolutePathWithAppstack description]
   */
  public function testJsonAbsolutePathWithAppstack(): void {
    // NOTE: due to internal usage of realpath
    // we simply workaround platform test differences
    // by inserting the platform-dependent directory separators right here
    $config = new \codename\core\config\json(__DIR__.DIRECTORY_SEPARATOR.'app1'.DIRECTORY_SEPARATOR.'sample.json', true, true);
    $this->assertEquals('value2', $config->get('key2'));
  }

  /**
   * config\json: First-match config loading
   */
  public function testJsonAppstackNoInheritance(): void {
    overrideableApp::reset();
    overrideableApp::__setApp('app1');
    overrideableApp::__setVendor('irrelevant');
    overrideableApp::__setHomedir(__DIR__.'/app1');

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected',
      'homedir' => __DIR__.'/app_injected',
      'namespace' => '--irrelevant--',
    ]);

    //
    // We traverse the appstack,
    // but we load the first matching config only.
    //
    $config = new \codename\core\config\json('otherSample.json', true, false);
    $this->assertEquals(true, $config->get('otherSample'));
  }

  /**
   * config\json\extendable: First-match config loading
   */
  public function testExtendedJsonAppstackNoInheritance(): void {
    overrideableApp::reset();
    overrideableApp::__setApp('app1');
    overrideableApp::__setVendor('irrelevant');
    overrideableApp::__setHomedir(__DIR__.'/app1');

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected',
      'homedir' => __DIR__.'/app_injected',
      'namespace' => '--irrelevant--',
    ]);

    //
    // We traverse the appstack,
    // but we load the first matching config only.
    //
    $config = new \codename\core\config\json\extendable('otherSample.json', true, false);
    $this->assertEquals(true, $config->get('otherSample'));
  }

  /**
   * config\json\extendable: Traverse appstack, but no file exists anywhere.
   */
  public function testExtendedJsonAppstackInheritanceNonexistingFile(): void {
    overrideableApp::reset();
    overrideableApp::__setApp('app1');
    overrideableApp::__setVendor('irrelevant');
    overrideableApp::__setHomedir(__DIR__.'/app1');

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected',
      'homedir' => __DIR__.'/app_injected',
      'namespace' => '--irrelevant--',
    ]);

    $this->expectExceptionMessage(\codename\core\config\json::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND);

    //
    // We traverse the appstack,
    // but we load the first matching config only.
    //
    $config = new \codename\core\config\json\extendable('nonexisting.json', true, true);
  }

  /**
   * config\json: Traverse appstack, but no file exists anywhere.
   */
  public function testJsonAppstackInheritanceNonexistingFile(): void {
    overrideableApp::reset();
    overrideableApp::__setApp('app1');
    overrideableApp::__setVendor('irrelevant');
    overrideableApp::__setHomedir(__DIR__.'/app1');

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected',
      'homedir' => __DIR__.'/app_injected',
      'namespace' => '--irrelevant--',
    ]);

    $this->expectExceptionMessage(\codename\core\config\json::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND);

    //
    // We traverse the appstack,
    // but we load the first matching config only.
    //
    $config = new \codename\core\config\json('nonexisting.json', true, true);
  }

  /**
   * Tests a more complex case with inheritance, appstack, mixins and extends
   * and keys that override each other
   */
  public function testExtendedJsonConfigAppstackInheritance(): void {
    overrideableApp::reset();
    overrideableApp::__setApp('app1');
    overrideableApp::__setVendor('irrelevant');
    overrideableApp::__setHomedir(__DIR__.'/app1');

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected',
      'homedir' => __DIR__.'/app_injected',
      'namespace' => '--irrelevant--',
    ]);

    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected_extends',
      'homedir' => __DIR__.'/app_injected_extends',
      'namespace' => '--irrelevant--',
    ]);
    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected_mixin',
      'homedir' => __DIR__.'/app_injected_mixin',
      'namespace' => '--irrelevant--',
    ]);
    overrideableApp::__injectApp([
      'vendor'  => 'irrelevant',
      'app'     => 'app_injected_overrides',
      'homedir' => __DIR__.'/app_injected_overrides',
      'namespace' => '--irrelevant--',
    ]);

    $config = new \codename\core\config\json\extendable('sample.json', true, true);

    $this->assertEquals('value1', $config->get('key1'));
    $this->assertEquals('value2', $config->get('key2'));
    $this->assertEquals('overridden', $config->get('overrideMe'));
    $this->assertEquals(true, $config->get('extend1'));
    $this->assertEquals(true, $config->get('mixin1'));
    $this->assertEquals(true, $config->get('extend1-extends'));
    $this->assertEquals(true, $config->get('mixin1-mixin'));

    $expectedInheritanceRegexes = [
      '/app_injected_mixin\/sample.json/',
      '/app_injected_extends\/sample.json/',
      '/app_injected\/sample.json/',
      '/app1\/sample.json/',
    ];

    $inheritance = $config->getInheritance();

    // make sure we have no unexpected inherited elements
    $this->assertCount(count($expectedInheritanceRegexes), $inheritance);

    foreach($expectedInheritanceRegexes as $index => $regex) {
      $this->assertMatchesRegularExpression($regex, $inheritance[$index]);
    }
  }

}
