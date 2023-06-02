<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the time validator
 * @package codename\core
 * @since 2016-11-02
 */
class time extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalideString(): void
    {
        static::assertEquals('VALIDATION.VALUE_INVALID_TIME_STRING', $this->getValidator()->validate('123')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsHoursInvalid(): void
    {
        static::assertEquals('VALIDATION.VALUE_INVALID_TIME_HOURS', $this->getValidator()->validate('25:10:10')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsMinutesInvalid(): void
    {
        static::assertEquals('VALIDATION.VALUE_INVALID_TIME_MINUTES', $this->getValidator()->validate('10:61:01')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsSecondsInvalid(): void
    {
        static::assertEquals('VALIDATION.VALUE_INVALID_TIME_SECONDS', $this->getValidator()->validate('10:10:61')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('01:02:03'));
    }
}
