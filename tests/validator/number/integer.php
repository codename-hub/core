<?php

namespace codename\core\tests\validator\number;

use codename\core\exception;
use codename\core\tests\validator\number;
use ReflectionException;

/**
 * base class for number validators
 */
class integer extends number
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooPrecise(): void
    {
        static::assertEquals('VALIDATION.VALUE_TOO_PRECISE', $this->getValidator()->validate(1.2)[0]['__CODE']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsInteger(): void
    {
        static::assertCount(0, $this->getValidator()->validate(345));
    }
}
