<?php

namespace codename\core\tests\session;

use codename\core\app;
use codename\core\exception;
use codename\core\session\cache;
use LogicException;
use ReflectionException;

/**
 * [cacheTest description]
 */
class cacheTest extends abstractSessionTest
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
    public function testBasicIo(): void
    {
        parent::testBasicIo();
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
    public function testClassInstance(): void
    {
        static::assertInstanceOf(cache::class, app::getSession());
    }

    /**
     * {@inheritDoc}
     */
    public function testExpiredSession(): void
    {
        static::markTestSkipped('Session expiry not applicable for this session driver.');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testSessionInvalidateThrowsException(): void
    {
        $this->expectException(LogicException::class);
        app::getSession()->invalidate('whatever');
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidSessionIdentify(): void
    {
        // Session invalidation is not supported in this session driver and will throw an exception
        $this->expectException(LogicException::class);
        parent::testInvalidateInvalidSession();
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidateSession(): void
    {
        // Session invalidation is not supported in this session driver and will throw an exception
        $this->expectException(LogicException::class);
        parent::testInvalidateSession();
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidateInvalidSession(): void
    {
        // Session invalidation is not supported in this session driver and will throw an exception
        $this->expectException(LogicException::class);
        parent::testInvalidateInvalidSession();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSessionConfig(): array
    {
        return [
          'driver' => 'cache',
        ];
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
        ];
    }
}
