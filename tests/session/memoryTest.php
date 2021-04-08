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
  public function testInvalidSession(): void
  {
    $this->markTestSkipped('Session invalid check not applicable for this session driver.');
  }

}
