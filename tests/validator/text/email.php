<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the email validator
 * @package codename\core
 * @since 2016-11-02
 */
class email extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotString(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate([])[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooLong(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidchars(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueAtNotFound(): void
    {
        static::assertEquals('VALIDATION.EMAIL_AT_NOT_FOUND', $this->getValidator()->validate('invalid')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueDomainInvalid(): void
    {
        $validationResult = $this->getValidator()->validate('invalid@');
        static::assertTrue(
            in_array(
                $validationResult[0]['__CODE'],
                [
                  'VALIDATION.EMAIL_DOMAIN_INVALID',
                  'VALIDATION.EMAIL_INVALID',
                ]
            )
        );
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueAtNotUnique(): void
    {
        $validationResult = $this->getValidator()->validate('invalid@sadas@as');
        static::assertTrue(
          in_array(
            $validationResult[0]['__CODE'],
            [
              'VALIDATION.EMAIL_AT_NOT_UNIQUE',
              'VALIDATION.EMAIL_INVALID',
            ]
          )
        );
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueDomainBlocked(): void
    {
        static::assertEquals('VALIDATION.EMAIL_DOMAIN_BLOCKED', $this->getValidator()->validate('invalid@whyspam.me')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueEmptyString(): void
    {
        static::assertEmpty($this->getValidator()->validate(''));
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        static::assertEquals('VALIDATION.EMAIL_INVALID', $this->getValidator()->validate('.@example.com')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('mymail@example.com'));
    }
}
