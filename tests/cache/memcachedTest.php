<?php

namespace codename\core\tests\cache;

use codename\core\cache;
use codename\core\cache\memcached;
use codename\core\tests\helper;
use Exception;

class memcachedTest extends abstractCacheTest
{
    /**
     * @return void
     */
    public function testInvalidEmptyConfiguration(): void
    {
        parent::testInvalidEmptyConfiguration();
    }

    /**
     * @return void
     */
    public function testSetCacheSimple(): void
    {
        parent::testSetCacheSimple();
    }

    /**
     * @return void
     */
    public function testSetCacheStructure(): void
    {
        parent::testSetCacheStructure();
    }

    /**
     * @return void
     */
    public function testSetCacheOverwrite(): void
    {
        parent::testSetCacheOverwrite();
    }

    /**
     * @return void
     */
    public function testClearKey(): void
    {
        parent::testClearKey();
    }

    /**
     * @return void
     */
    public function testFlush(): void
    {
        parent::testFlush();
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // wait for host to come up
        if (getenv('unittest_core_cache_memcached_host')) {
            if (!helper::waitForIt(getenv('unittest_core_cache_memcached_host'), (int)getenv('unittest_core_cache_memcached_port'), 3, 3, 5)) {
                throw new Exception('Failed to connect to memcached server');
            }
        } else {
            static::markTestSkipped('memcached host unavailable');
        }
    }

    /**
     * {@inheritDoc}
     * @param array|null $config
     * @return cache
     * @throws \codename\core\exception
     */
    public function getCache(?array $config = null): cache
    {
        if ($config === null) {
            $config = [
                // default config?
              'driver' => 'memcached',
              'host' => getenv('unittest_core_cache_memcached_host'),
              'port' => getenv('unittest_core_cache_memcached_port'),
            ];
        }

        return new memcached($config);
    }

    /**
     * {@inheritDoc}
     */
    public function testClearGroup(): void
    {
        static::markTestSkipped('memcached::clearGroup is based on Memcached::getAllKeys() which is not fully supported');
//        parent::testClearGroup();
    }
}
