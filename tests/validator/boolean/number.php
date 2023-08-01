<?php

namespace codename\core\tests\validator\boolean;

use codename\core\exception;
use codename\core\tests\validator\boolean;
use ReflectionException;

/**
 * base class for boolean validators
 */
class number extends boolean
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNotBooleanNumber(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_NUMERIC_BOOLEAN', $this->getValidator()->validate(2)[0]['__CODE']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsBooleanNumber(): void
    {
        static::assertCount(0, $this->getValidator()->validate(1));
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsBoolean(): void
    {
        static::assertCount(0, $this->getValidator()->validate(true));
    }
}
