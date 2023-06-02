<?php

namespace codename\core\tests\database;

use codename\core\database;
use codename\core\exception;
use codename\core\sensitiveException;
use codename\core\tests\base;

/**
 * Base model class performing cross-platform/technology tests with model classes
 */
class databaseTest extends base
{
    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testDatabaseMissingPassword(): void
    {
        $this->expectExceptionMessage(database::EXCEPTION_CONSTRUCT_CONNECTIONERROR);
        new database([]);
    }

    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testDatabaseMissingHost(): void
    {
        $this->expectExceptionMessage(database::EXCEPTION_CONSTRUCT_CONNECTIONERROR);
        new database([
          'pass' => 'test',
        ]);
    }

    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testDatabaseMissingHostEnvPassGiven(): void
    {
        $this->expectExceptionMessage(database::EXCEPTION_CONSTRUCT_CONNECTIONERROR);
        new database([
          'env_pass' => 'some_key',
        ]);
    }

    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testDatabaseMissingUser(): void
    {
        $this->expectExceptionMessage(database::EXCEPTION_CONSTRUCT_CONNECTIONERROR);
        new database([
          'pass' => 'test',
          'host' => 'test',
        ]);
    }

    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testDatabaseMissingUserEnvGiven(): void
    {
        $this->expectExceptionMessage(database::EXCEPTION_CONSTRUCT_CONNECTIONERROR);
        new database([
          'env_pass' => 'test',
          'env_host' => 'test',
        ]);
    }

    /**
     * @return void
     * @throws exception
     * @throws sensitiveException
     */
    public function testEnvBasedConfig(): void
    {
        // Actually, this is a PDO Exception message
        // as we try to trick the database driver into passing the generic config checks
        // and run into the problem that the base driver does NOT define a PDO driver to use.
        $this->expectExceptionMessage('could not find driver');
        putenv('databaseTest_env_pass_key=pass_value');
        putenv('databaseTest_env_host_key=user_value');
        putenv('databaseTest_env_user_key=host_value');

        new database([
          'env_pass' => 'databaseTest_env_pass_key',
          'env_host' => 'databaseTest_env_host_key',
          'env_user' => 'databaseTest_env_user_key',
          'database' => 'xyz',
        ]);
    }
}
