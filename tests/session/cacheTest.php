<?php
namespace codename\core\tests\bucket;

use codename\core\app;

class cacheTest extends abstractSessionTest {
  /**
   * @inheritDoc
   */
  protected function getDefaultSessionConfig(): array
  {
    return [
      'driver' => 'cache'
    ];
  }

  /**
   * @inheritDoc
   */
  protected function getAdditionalEnvironmentConfig(): array
  {
    return [
      'cache' => [
        'default' => [
          'driver' => 'memory',
        ]
      ]
    ];
  }

  /**
   * [testClassInstance description]
   */
  public function testClassInstance(): void {
    $this->assertInstanceOf(\codename\core\session\cache::class, app::getSession());
  }
}
