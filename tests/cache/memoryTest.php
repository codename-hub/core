<?php
namespace codename\core\tests\cache;

use codename\core\tests\cache\abstractCacheTest;

class memoryTest extends abstractCacheTest {

  /**
   * @inheritDoc
   */
  public function getCache(?array $config = null): \codename\core\cache
  {
    if($config === null) {
      $config = [
        // default config?
        'driver'    => 'memory',
      ];
    }

    return new \codename\core\cache\memory($config);
  }

  /**
   * @inheritDoc
   */
  public function testInvalidEmptyConfiguration(): void
  {
    $this->markTestSkipped('Empty configuration is allowed for bare-memory cache');
    parent::testInvalidEmptyConfiguration();
  }
}
