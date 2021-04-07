<?php
namespace codename\core\tests\config;

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
   * [testExtendedJsonConfig description]
   */
  public function testExtendedJsonConfig(): void {
    $config = new \codename\core\config\json\extendable('tests/config/example.extends.json');
    $this->assertEquals('some-overridden-value', $config->get('some-key'));
    $this->assertEquals('value1',         $config->get('some-object>key1'));
    $this->assertEquals('value-changed',  $config->get('some-object>key2'));
    $this->assertEquals('value3',         $config->get('some-object>key3'));
    $this->assertEquals('value-added',    $config->get('some-object>key4'));

    // TODO: arrays?

    // print_r($config->get());
  }

}
