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
   * @inheritDoc
   */
  protected function getDatabaseInstance(array $config): \codename\core\database
  {
    return new \codename\core\database\sqlite($config);
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
  protected function getJoinNestingLimit(): int
  {
    return 64;
  }

  /**
   * @inheritDoc
   */
  public function testAggregateDatetimeInvalid(): void
  {
    $this->expectExceptionMessage('EXCEPTION_MODEL_PLUGIN_CALCULATION_SQLITE_UNKKNOWN_CALCULATION_TYPE');
    parent::testAggregateDatetimeInvalid();
  }

}
