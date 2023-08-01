<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the telephone validator
 * @package codename\core
 * @since 2016-11-02
 */
class telephone extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooLong(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('+346759823475982347659234759234865923487562394875692384756923487659238476598237652398756329876')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidchars(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('459"§!§345934')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('+496622918818'));
    }
}
