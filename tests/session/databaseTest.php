<?php

namespace codename\core\tests\session;

use codename\core\app;
use codename\core\exception;
use codename\core\model;
use codename\core\session\database;
use codename\core\tests\overrideableApp;
use ReflectionException;

class databaseTest extends abstractSessionTest
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testUnidentified(): void
    {
        parent::testUnidentified();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testEmulatedSessionIo(): void
    {
        parent::testEmulatedSessionIo();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testInvalidSessionIdentify(): void
    {
        parent::testInvalidSessionIdentify();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testExpiredSession(): void
    {
        parent::testExpiredSession();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testInvalidateSession(): void
    {
        parent::testInvalidateSession();
    }

    /**
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$initialized = false;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testClassInstance(): void
    {
        static::assertInstanceOf(database::class, app::getSession());
    }

    /**
     * {@inheritDoc}
     */
    public function testBasicIo(): void
    {
        static::markTestSkipped('Generic BasicIo test for database-session not applicable due to cookies');
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidateInvalidSession(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_SESSION_INVALIDATE_NO_SESSIONID_PROVIDED');
        parent::testInvalidateInvalidSession();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSessionConfig(): array
    {
        return [
          'driver' => 'database',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // avoid re-init
        if (!static::$initialized) {
            static::$initialized = true;

            static::createModel('testschema', 'session', [
              "field" => [
                "session_id",
                "session_created",
                "session_modified",
                "session_valid",
                "session_valid_until",
                "session_data",
                "session_sessionid",
              ],
              "primary" => [
                "session_id",
              ],
              "index" => [
                "session_sessionid",
                "session_created",
                "session_valid",
                ["session_sessionid", "session_valid"],
              ],
              "options" => [
                "session_sessionid" => [
                  "length" => 128,
                ],
              ],
              "datatype" => [
                "session_id" => "number_natural",
                "session_created" => "text_timestamp",
                "session_modified" => "text_timestamp",
                "session_valid" => "boolean",
                "session_valid_until" => "text_timestamp",
                "session_data" => "structure",
                "session_sessionid" => "text",
              ],
              "connection" => "default",
            ]);

            static::architect('sessiontest', 'codename', 'test');
        }

        $sessionModel = $this->getModel('session');
        $sessionClient = new sessionDatabaseOverridden([], $sessionModel);
        overrideableApp::__injectClientInstance('session', 'default', $sessionClient);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAdditionalEnvironmentConfig(): array
    {
        return [
          'cache' => [
            'default' => [
              'driver' => 'memory',
            ],
          ],
          'database' => [
            'default' => [
              'driver' => 'sqlite',
              'database_file' => ':memory:',
            ],
          ],
          'filesystem' => [
            'local' => [
              'driver' => 'local',
            ],
          ],
        ];
    }

    /**
     * {@inheritDoc}
     * @param array|null $data
     * @throws ReflectionException
     * @throws exception
     */
    protected function emulateSession(?array $data): void
    {
        if ($data) {
            $cookieValue = $data['identifier'];
            $_COOKIE['core-session'] = $cookieValue;
            $this->getModel('session')->save([
              'session_sessionid' => $cookieValue,
              'session_valid' => $data['valid'] ?? true,
              'session_valid_until' => $data['valid_until'] ?? null,
            ]);
        } else {
            unset($_COOKIE['core-session']);
        }
    }
}

class sessionDatabaseOverridden extends database
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $data, model $sessionModelInstance)
    {
        $this->sessionModel = $sessionModelInstance;
    }
}
