<?php

namespace codename\core\tests\validator\text\datetime;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the relative validator
 * @package codename\core
 * @since 2016-11-02
 */
class relative extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        static::assertEquals('VALIDATION.INVALID_RELATIVE_DATETIME', $this->getValidator()->validate('won')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('now'));
    }
}
