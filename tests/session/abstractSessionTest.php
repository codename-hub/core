<?php

namespace codename\core\tests\session;

use codename\core\app;
use codename\core\exception;
use codename\core\tests\base;
use DateTime;
use ReflectionException;

abstract class abstractSessionTest extends base
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testUnidentified(): void
    {
        $this->emulateSession(null);
        static::assertFalse(app::getSession()->identify(), var_export(app::getSession()->getData(), true));
    }

    /**
     * Emulates a session (or none, if null)
     * is key 'valid' is not supplied, we automatically assume
     * a valid session to be emulated
     * $data might be an array of one or more of these keys:
     * (string) identifier    (e.g. a cookie value or header)
     * (bool)   valid         (whether the session was recently evaluated as valid)
     * (string) valid_until   (ISO datetime of expiry)
     * @param array|null $data
     * @return void
     */
    protected function emulateSession(?array $data): void
    {
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testBasicIo(): void
    {
        $this->emulateSession([]);
        static::assertFalse(app::getSession()->identify());

        app::getSession()->start([
          'session_data' => [
            'dummy' => true,
          ],
          'dummy' => true,
        ]);

        static::assertTrue(app::getSession()->identify());
        static::assertTrue(app::getSession()->isDefined('dummy'));
        static::assertFalse(app::getSession()->isDefined('nonexisting'));

        static::assertTrue(app::getSession()->getData('dummy'));

        app::getSession()->setData('dummy', 'some-value');
        static::assertEquals('some-value', app::getSession()->getData('dummy'));

        // TODO: Not supported for every driver right now:
        // app::getSession()->unsetData('dummy');
        // static::assertFalse(app::getSession()->isDefined('dummy'));

        app::getSession()->destroy();

        static::assertFalse(app::getSession()->identify());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testEmulatedSessionIo(): void
    {
        // Emulate a nonexisting session
        $this->emulateSession(null);
        static::assertFalse(app::getSession()->identify());

        // Emulate an existing session afterward
        $this->emulateSession([
          'identifier' => 'some-random-session',
        ]);

        // due to cookie limitations on CLI
        // this might throw a WarningException, if not suppressed this way
        @app::getSession()->start([
          'session_data' => [
            'dummy' => true,
          ],
          'dummy' => true,
        ]);

        static::assertTrue(app::getSession()->identify());
        // print_r(app::getSession()->getData());
        static::assertTrue(app::getSession()->isDefined('dummy'));
        static::assertFalse(app::getSession()->isDefined('nonexisting'));

        static::assertTrue(app::getSession()->getData('dummy'));

        app::getSession()->setData('dummy', 'some-value');
        static::assertEquals('some-value', app::getSession()->getData('dummy'));

        // TODO: Not supported for every driver right now:
        // app::getSession()->unsetData('dummy');
        // static::assertFalse(app::getSession()->isDefined('dummy'));

        // due to cookie limitations on CLI
        // this might throw a WarningException, if not suppressed this way
        @app::getSession()->destroy();

        static::assertFalse(app::getSession()->identify());

        // Emulate a nonexisting session again
        $this->emulateSession(null);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInvalidSessionIdentify(): void
    {
        // Emulate an existing session
        $this->emulateSession([
          'identifier' => 'some-valid-session',
          'valid' => true,
        ]);
        static::assertTrue(app::getSession()->identify());

        $this->emulateSession([
          'identifier' => 'some-invalid-session',
          'valid' => false,
        ]);
        static::assertFalse(app::getSession()->identify());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testExpiredSession(): void
    {
        // Emulate an existing session
        $this->emulateSession([
          'identifier' => 'some-expired-session',
          'valid' => true,
          'valid_until' => (new DateTime('now'))->modify('- 1 day')->format('Y-m-d H:i:s'),
        ]);
        static::assertFalse(@app::getSession()->identify());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInvalidateSession(): void
    {
        // Emulate an existing session
        $this->emulateSession([
          'identifier' => 'some-valid-session',
          'valid' => true,
        ]);

        @app::getSession()->start([
          'session_data' => [
            'dummy' => true,
          ],
          'dummy' => true,
        ]);

        static::assertTrue(app::getSession()->identify());
        try {
            app::getSession()->invalidate('some-valid-session');
        } catch (exception) {
            static::fail();
        }
        static::assertFalse(app::getSession()->identify());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function testInvalidateInvalidSession(): void
    {
        app::getSession()->invalidate('');
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => array_merge(
              [
                'session' => [
                  'default' => $this->getDefaultSessionConfig(),
                ],
              ],
              $this->getAdditionalEnvironmentConfig()
          ),
        ]);
    }

    /**
     * should return a database config for 'default' connection
     * @return array
     */
    abstract protected function getDefaultSessionConfig(): array;

    /**
     * @return array [description]
     */
    protected function getAdditionalEnvironmentConfig(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        $this->emulateSession(null);
        app::getSession()->destroy();
        parent::tearDown();
    }
}
