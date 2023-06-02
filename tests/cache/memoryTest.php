<?php

namespace codename\core\tests\cache;

use codename\core\cache;
use codename\core\cache\memory;

class memoryTest extends abstractCacheTest
{
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
    public function testClearGroup(): void
    {
        parent::testClearGroup();
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
     */
    public function getCache(?array $config = null): cache
    {
        if ($config === null) {
            $config = [
                // default config?
              'driver' => 'memory',
            ];
        }

        return new memory($config);
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidEmptyConfiguration(): void
    {
        static::markTestSkipped('Empty configuration is allowed for bare-memory cache');
//        parent::testInvalidEmptyConfiguration();
    }
}
