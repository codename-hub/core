<?php
namespace codename\core\tests\model\schematic;

use codename\core\app;

use codename\core\tests\model\abstractModelTest;

class mysqlTest extends abstractModelTest {
  /**
   * @inheritDoc
   */
  protected function getDefaultDatabaseConfig(): array
  {
    return [
      'driver'    => 'mysql',
      'host'      => getenv('unittest_core_db_mysql_host'),
      'user'      => getenv('unittest_core_db_mysql_user'),
      'pass'      => getenv('unittest_core_db_mysql_pass'),
      'database'  => getenv('unittest_core_db_mysql_database'),
      'autoconnect_database' => false,
      'port'      => 3306,
      'charset'   => 'utf8',
    ];
  }

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for rmysql to come up
    if(getenv('unittest_core_db_mysql_host')) {
      if(!\codename\core\tests\helper::waitForIt(getenv('unittest_core_db_mysql_host'), 3306, 3, 3, 5)) {
        throw new \Exception('Failed to connect to mysql server');
      }
    } else {
      static::markTestSkipped('Mysql host unavailable');
    }
  }

  /**
   * @inheritDoc
   */
  public function testConnectionErrorExceptionIsSensitive(): void
  {
    //
    // Important test to make sure a database error exception
    // doesn't leak any credentials (wrapped in a sensitiveException)
    //
    $this->expectException(\codename\core\sensitiveException::class);
    $this->getDatabaseInstance([
      'driver' => 'mysql',
      'host' => 'nonexisting-host',
      'user' => 'some-username',
      'pass' => 'some-password',
    ]);
  }

  /**
   * @inheritDoc
   */
  protected function getDatabaseInstance(array $config): \codename\core\database
  {
    return new \codename\core\database\mysql($config);
  }

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    // shutdown the mysql server
    // At this point, we assume the DB data is stored on a volatile medium
    // to implicitly clear all data after shutdown

    // NOTE/WARNING: shutdown on the DB Server
    // will crash PDO connections that try to close themselves
    // after testing - STMT_CLOSE will fail due to passed-away DB
    // in a more or less random fashion.
    // As long as you use volatile containers, everything will be fine.
    // app::getDb('default')->query('SHUTDOWN;');

    parent::tearDownAfterClass();
  }

  /**
   * [testInstanceClass description]
   */
  public function testInstanceClass(): void {
    $this->assertInstanceOf(\codename\core\database\mysql::class, \codename\core\app::getDb());
  }
}
