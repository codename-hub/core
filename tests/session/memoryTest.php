<?php

namespace codename\core\tests\session;

use codename\core\app;
use codename\core\exception;
use codename\core\session\memory;
use LogicException;
use ReflectionException;

class memoryTest extends abstractSessionTest
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
        static::assertInstanceOf(memory::class, app::getSession());
    }

    /**
     * {@inheritDoc}
     */
    public function testExpiredSession(): void
    {
        static::markTestSkipped('Session expiry not applicable for this session driver.');
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidSessionIdentify(): void
    {
        //
        // NOTE: this is a test for testing session validity check - nothing else.
        // For this driver, this is unavailable anyway and *must* be overridden to ::markTestSkipped()
        //
        static::markTestSkipped('Session invalid check not applicable for this session driver.');
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
          'driver' => 'memory',
        ];
    }
}
