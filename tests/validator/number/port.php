<?php

namespace codename\core\tests\validator\number;

use codename\core\exception;
use codename\core\tests\validator\number;
use ReflectionException;

/**
 * base class for number validators
 */
class port extends number
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooPrecise(): void
    {
        static::assertEquals('VALIDATION.VALUE_TOO_PRECISE', $this->getValidator()->validate(15.123)[0]['__CODE']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsSomePort(): void
    {
        static::assertCount(0, $this->getValidator()->validate(3306));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooSmall(): void
    {
        static::assertEquals('VALIDATION.VALUE_TOO_SMALL', $this->getValidator()->validate(0)[0]['__CODE']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooBig(): void
    {
        static::assertEquals('VALIDATION.VALUE_TOO_BIG', $this->getValidator()->validate(65536)[0]['__CODE']);
    }
}
