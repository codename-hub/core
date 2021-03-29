<?php
namespace codename\core\tests\cache;

use codename\core\tests\base;

abstract class abstractCacheTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    static::setEnvironmentConfig([
      'test' => [
        // 'filesystem' =>[
        //   'local' => [
        //     'driver' => 'local',
        //   ]
        // ],
        'log' => [
          'debug' => [
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
   * [getCache description]
   * @param  array|null $config
   * @return \codename\core\cache [description]
   */
  public abstract function getCache(?array $config = null): \codename\core\cache;

  /**
  * [testInvalidEmptyConfiguration description]
  */
  public function testInvalidEmptyConfiguration(): void {
    $this->expectException(\codename\core\exception::class);
    // Simply pass an empty configuration array
    $cache = $this->getCache([]);
  }

  /**
   * [testSetCacheSimple description]
   */
  public function testSetCacheSimple(): void {
    $cache = $this->getCache();
    $cache->set('test', 'simple', 'example');
    $this->assertEquals('example', $cache->get('test', 'simple'));
  }

  /**
   * [testSetCacheStructure description]
   */
  public function testSetCacheStructure(): void {
    $cache = $this->getCache();
    $cache->set('test', 'structure', [ 'some_key' => 'some_value' ]);
    $this->assertEquals([ 'some_key' => 'some_value' ], $cache->get('test', 'structure'));
  }

  /**
   * [testSetCacheOverwrite description]
   */
  public function testSetCacheOverwrite(): void {
    $cache = $this->getCache();
    $cache->set('test', 'overwritten', 'first_value');
    $cache->set('test', 'overwritten', 'second_value');
    $this->assertEquals('second_value', $cache->get('test', 'overwritten'));
  }

  /**
   * [testClearKey description]
   */
  public function testClearKey(): void {
    $cache = $this->getCache();
    $cache->set('test', 'clear_me', 'some_value');
    $cache->clearKey('test', 'clear_me');
    $this->assertFalse($cache->isDefined('test', 'clear_me'));
    $this->assertNull($cache->get('test', 'clear_me'));
  }

  /**
   * [testClearGroup description]
   */
  public function testClearGroup(): void {
    $cache = $this->getCache();
    $cache->set('some_group', 'key1', 'some_value');
    $cache->set('some_group', 'key2', 'another_value');
    // make sure they exist, first
    $this->assertTrue($cache->isDefined('some_group', 'key1'));
    $this->assertTrue($cache->isDefined('some_group', 'key2'));
    $cache->clearGroup('some_group');
    $this->assertFalse($cache->isDefined('some_group', 'key1'));
    $this->assertFalse($cache->isDefined('some_group', 'key2'));
    $this->assertNull($cache->get('some_group', 'key1'));
    $this->assertNull($cache->get('some_group', 'key1'));
  }

  /**
   * [testFlush description]
   */
  public function testFlush(): void {
    $cache = $this->getCache();
    $cache->set('flushgroup1', 'key1', 'some_value');
    $cache->set('flushgroup2', 'key2', 'another_value');
    $this->assertTrue($cache->isDefined('flushgroup1', 'key1'));
    $this->assertTrue($cache->isDefined('flushgroup2', 'key2'));

    // Flush all entries
    $cache->flush();
    $this->assertFalse($cache->isDefined('flushgroup1', 'key1'));
    $this->assertFalse($cache->isDefined('flushgroup2', 'key2'));
  }

}
