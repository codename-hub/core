<?php
namespace codename\core\tests\session;

use codename\core\app;

class memoryTest extends abstractSessionTest {
  /**
   * @inheritDoc
   */
  protected function getDefaultSessionConfig(): array
  {
    return [
      'driver' => 'memory'
    ];
  }

  /**
   * [testClassInstance description]
   */
  public function testClassInstance(): void {
    $this->assertInstanceOf(\codename\core\session\memory::class, app::getSession());
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
  public function testInvalidSessionIdentify(): void
  {
    //
    // NOTE: this is a test for testing session validity check - nothing else.
    // For this driver, this is unavailable anyways and *must* be overridden to ::markTestSkipped()
    //
    $this->markTestSkipped('Session invalid check not applicable for this session driver.');
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
