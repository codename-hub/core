<?php
namespace codename\core\tests\model\schematic;

use codename\core\tests\model\abstractModelTest;

class sqliteTest extends abstractModelTest {
  /**
   * @inheritDoc
   */
  protected function getDefaultDatabaseConfig(): array
  {
    return [
      'driver' => 'sqlite',
      'database_file' => ':memory:',
    ];
  }

  /**
   * [testInstanceClass description]
   */
  public function testInstanceClass(): void {
    $this->assertInstanceOf(\codename\core\database\sqlite::class, \codename\core\app::getDb());
  }

}
