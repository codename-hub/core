<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the apploader validator
 * @package codename\core
 * @since 2016-11-02
 */
class apploader extends text
{
    /**
     * apploader value must be lowercase
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextApploaderValueStringMustBeLowercase(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate(123)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueStringTooShort(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('a')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueStringTooLong(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueStringContainsInvalidCharacters(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('codename\\core'));
    }
}
