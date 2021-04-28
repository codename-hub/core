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
   * [testSessionInvalidateThrowsException description]
   */
  public function testSessionInvalidateThrowsException(): void {
    $this->expectException(\LogicException::class);
    app::getSession()->invalidate('whatever');
  }

  /**
   * @inheritDoc
   */
  public function testInvalidSessionIdentify(): void
  {
    $this->markTestSkipped('Session identification works differently on cache driver.');
  }

  /**
   * @inheritDoc
   */
  public function testInvalidateSession(): void
  {
    // Session invalidation is not supported in this session driver and will throw an exception
    $this->expectException(\LogicException::class);
    parent::testInvalidateSession();
  }

  /**
   * @inheritDoc
   */
  public function testInvalidateInvalidSession(): void
  {
    // Session invalidation is not supported in this session driver and will throw an exception
    $this->expectException(\LogicException::class);
    parent::testInvalidateInvalidSession();
  }
}
