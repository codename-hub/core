<?php

namespace codename\core\tests\validator\structure\text;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the telephone validator
 * @package codename\core
 * @since 2016-11-02
 */
class telephone extends structure
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidPhoneNumber(): void
    {
        static::assertEquals('VALIDATION.INVALID_PHONE_NUMBER', $this->getValidator()->validate(['AAA'])[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate([]));
    }
}
