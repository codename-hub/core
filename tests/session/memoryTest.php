<?php
namespace codename\core\tests\bucket;

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
}
