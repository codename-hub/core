<?php
namespace codename\core\tests\session;

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

  /**
   * @inheritDoc
   */
  public function testExpiredSession(): void
  {
    $this->markTestSkipped('Session expiry not applicable for this session driver.');
  }

  /**
   * @inheritDoc
   */
  public function testInvalidSession(): void
  {
    $this->expectException(\LogicException::class);
    app::getSession()->invalidate('example');
  }
}
