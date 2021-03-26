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

  /**
   * @inheritDoc
   */
  public function testAggregateDatetimeQuarter(): void
  {
    $this->addWarning('SQLite doesn\'t support QUARTER');
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_PLUGIN_CALCULATION_SQLITE_UNKKNOWN_CALCULATION_TYPE');
    parent::testAggregateDatetimeQuarter();
  }
}
