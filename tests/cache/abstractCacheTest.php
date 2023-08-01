<?php

namespace codename\core\tests\cache;

use codename\core\cache;
use codename\core\exception;
use codename\core\tests\base;

abstract class abstractCacheTest extends base
{
    /**
     * @return void
     */
    protected function testInvalidEmptyConfiguration(): void
    {
        $this->expectException(exception::class);
        // Simply pass an empty configuration array
        $this->getCache([]);
    }

    /**
     * @param array|null $config
     * @return cache
     */
    abstract public function getCache(?array $config = null): cache;

    /**
     * @return void
     */
    protected function testSetCacheSimple(): void
    {
        $cache = $this->getCache();
        $cache->set('test', 'simple', 'example');
        static::assertEquals('example', $cache->get('test', 'simple'));
    }

    /**
     * @return void
     */
    protected function testSetCacheStructure(): void
    {
        $cache = $this->getCache();
        $cache->set('test', 'structure', ['some_key' => 'some_value']);
        static::assertEquals(['some_key' => 'some_value'], $cache->get('test', 'structure'));
    }

    /**
     * @return void
     */
    protected function testSetCacheOverwrite(): void
    {
        $cache = $this->getCache();
        $cache->set('test', 'overwritten', 'first_value');
        $cache->set('test', 'overwritten', 'second_value');
        static::assertEquals('second_value', $cache->get('test', 'overwritten'));
    }

    /**
     * @return void
     */
    protected function testClearKey(): void
    {
        $cache = $this->getCache();
        $cache->set('test', 'clear_me', 'some_value');
        $cache->clearKey('test', 'clear_me');
        static::assertFalse($cache->isDefined('test', 'clear_me'));
        static::assertNull($cache->get('test', 'clear_me'));
    }

    /**
     * @return void
     */
    protected function testClearGroup(): void
    {
        $cache = $this->getCache();
        $cache->set('some_group', 'key1', 'some_value');
        $cache->set('some_group', 'key2', 'another_value');
        // make sure they exist, first
        static::assertTrue($cache->isDefined('some_group', 'key1'));
        static::assertTrue($cache->isDefined('some_group', 'key2'));
        $cache->clearGroup('some_group');
        static::assertFalse($cache->isDefined('some_group', 'key1'));
        static::assertFalse($cache->isDefined('some_group', 'key2'));
        static::assertNull($cache->get('some_group', 'key1'));
        static::assertNull($cache->get('some_group', 'key1'));
    }

    /**
     * @return void
     */
    protected function testFlush(): void
    {
        $cache = $this->getCache();
        $cache->set('flushgroup1', 'key1', 'some_value');
        $cache->set('flushgroup2', 'key2', 'another_value');
        static::assertTrue($cache->isDefined('flushgroup1', 'key1'));
        static::assertTrue($cache->isDefined('flushgroup2', 'key2'));

        // Flush all entries
        $cache->flush();
        static::assertFalse($cache->isDefined('flushgroup1', 'key1'));
        static::assertFalse($cache->isDefined('flushgroup2', 'key2'));
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        static::setEnvironmentConfig([
          'test' => [
            'log' => [
              'debug' => [
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
