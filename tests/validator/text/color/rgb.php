<?php

namespace codename\core\tests\validator\text\color;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the rgb validator
 * @package codename\core
 * @since 2016-11-02
 */
class rgb extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooShort(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('1')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooLong(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidchars(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD123456')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidRgbString(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_RGB_STRING', $this->getValidator()->validate('rgb(rgb, 255, 0)')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('rgb(223, 255, 0)'));
    }
}
