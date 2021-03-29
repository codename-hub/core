<?php
namespace codename\core\tests\cache;

use codename\core\tests\cache\abstractCacheTest;

class memcachedTest extends abstractCacheTest {

  /**
   * @inheritDoc
   */
  public function getCache(?array $config = null): \codename\core\cache
  {
    if($config === null) {
      $config = [
        // default config?
        'driver'    => 'memcached',
        'host'      => getenv('unittest_core_cache_memcached_host'),
        'port'      => getenv('unittest_core_cache_memcached_port'),
      ];
    }

    return new \codename\core\cache\memcached($config);
  }

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for host to come up
    if(getenv('unittest_core_cache_memcached_host')) {
      if(!\codename\core\tests\helper::waitForIt(getenv('unittest_core_cache_memcached_host'), (int)getenv('unittest_core_cache_memcached_port'), 3, 3, 5)) {
        throw new \Exception('Failed to connect to memcached server');
      }
    } else {
      static::markTestSkipped('memcached host unavailable');
    }
  }

  /**
   * @inheritDoc
   */
  public function testClearGroup(): void
  {
    $this->markTestSkipped('memcached::clearGroup is based on Memcached::getAllKeys() which is not fully supported');
    parent::testClearGroup();
  }
}
