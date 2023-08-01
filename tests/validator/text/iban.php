<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the iban validator
 * @package codename\core
 * @since 2016-11-02
 */
class iban extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidchars(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('DE7953290000001042200.')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueCountryNotFound(): void
    {
        static::assertEquals('VALIDATION.IBAN_COUNTRY_NOT_FOUND', $this->getValidator()->validate('XH13127953290000001042200')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueLengthMismatch(): void
    {
        static::assertEquals('VALIDATION.IBAN_LENGTH_NOT_MATCHING_COUNTRY', $this->getValidator()->validate('DE795329000000104220001')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueChecksumMismatch(): void
    {
        static::assertEquals('VALIDATION.IBAN_CHECKSUM_FAILED', $this->getValidator()->validate('DE42532900000010422000')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('DE79532900000010422000'));
    }
}
